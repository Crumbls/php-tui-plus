<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\WizardWidget;
use Crumbls\Tui\Extension\Core\Widget\Wizard\Step;

/**
 * Wizard Component Tests
 *
 * A multi-step flow component that guides users through a sequential process.
 * Each step can contain its own components and validation.
 */

// =============================================================================
// NAVIGATION TESTS
// =============================================================================

describe('navigation', function (): void {
    test('next() advances to next step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
                Step::make('third')->title('Third'),
            ]);

        expect($widget->getCurrentStepIndex())->toBe(0);

        $widget->next();

        expect($widget->getCurrentStepIndex())->toBe(1);
    });

    test('back() returns to previous step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->currentStep(1);

        expect($widget->getCurrentStepIndex())->toBe(1);

        $widget->back();

        expect($widget->getCurrentStepIndex())->toBe(0);
    });

    test('cannot go back from first step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        expect($widget->canGoBack())->toBeFalse();
        expect($widget->isFirstStep())->toBeTrue();

        $widget->back();

        expect($widget->getCurrentStepIndex())->toBe(0);
    });

    test('cannot go next from last step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->currentStep(1);

        expect($widget->canGoNext())->toBeFalse();
        expect($widget->isLastStep())->toBeTrue();

        $widget->next();

        expect($widget->getCurrentStepIndex())->toBe(1);
    });

    test('validation prevents next on invalid', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(fn($data) => isset($data['required_field']) ?: 'Field is required'),
                Step::make('second')->title('Second'),
            ]);

        $widget->next();

        expect($widget->getCurrentStepIndex())->toBe(0);
        expect($widget->getStepError('first'))->toBe('Field is required');
    });

    test('goToStep works for non-linear mode', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
                Step::make('third')->title('Third'),
            ])
            ->linear(false);

        $widget->goToStep(2);

        expect($widget->getCurrentStepIndex())->toBe(2);
    });
});

// =============================================================================
// VALIDATION TESTS
// =============================================================================

describe('validation', function (): void {
    test('validate() calls step validate function', function (): void {
        $validated = false;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(function ($data) use (&$validated) {
                        $validated = true;
                        return true;
                    }),
            ]);

        $widget->validate();

        expect($validated)->toBeTrue();
    });

    test('invalid step shows error message', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(fn($data) => 'This is an error'),
            ]);

        $widget->validate();

        expect($widget->getStepError('first'))->toBe('This is an error');
    });

    test('valid step clears error', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(fn($data) => isset($data['name']) ?: 'Name required'),
            ]);

        $widget->validate();
        expect($widget->getStepError('first'))->toBe('Name required');

        $widget->setData('name', 'Test');
        $widget->validate();

        expect($widget->getStepError('first'))->toBeNull();
    });

    test('beforeLeave can prevent navigation', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->beforeLeave(fn($data, $toStep) => false),
                Step::make('second')->title('Second'),
            ]);

        $widget->next();

        expect($widget->getCurrentStepIndex())->toBe(0);
    });

    test('beforeEnter can prevent navigation', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')
                    ->title('Second')
                    ->beforeEnter(fn($data, $fromStep) => false),
            ]);

        $widget->next();

        expect($widget->getCurrentStepIndex())->toBe(0);
    });
});

// =============================================================================
// DATA TESTS
// =============================================================================

describe('data', function (): void {
    test('setData updates wizard data', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
            ]);

        $widget->setData('name', 'Test Value');

        expect($widget->getData())->toHaveKey('name');
        expect($widget->getData()['name'])->toBe('Test Value');
    });

    test('getData returns all data', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
            ])
            ->data(['key1' => 'value1', 'key2' => 'value2']);

        $data = $widget->getData();

        expect($data)->toBe(['key1' => 'value1', 'key2' => 'value2']);
    });

    test('step components receive current data', function (): void {
        $receivedData = null;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->component(function ($data) use (&$receivedData) {
                        $receivedData = $data;
                        return null;
                    }),
            ])
            ->data(['existing' => 'data']);

        $widget->getCurrentStep()->renderComponent($widget->getData());

        expect($receivedData)->toBe(['existing' => 'data']);
    });

    test('onChange fires on data updates', function (): void {
        $changedField = null;
        $changedValue = null;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
            ])
            ->onChange(function ($field, $value, $data) use (&$changedField, &$changedValue): void {
                $changedField = $field;
                $changedValue = $value;
            });

        $widget->setData('test_field', 'test_value');

        expect($changedField)->toBe('test_field');
        expect($changedValue)->toBe('test_value');
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onStepChange fires on navigation', function (): void {
        $fromStep = null;
        $toStep = null;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->onStepChange(function ($from, $to, $data) use (&$fromStep, &$toStep): void {
                $fromStep = $from;
                $toStep = $to;
            });

        $widget->next();

        expect($fromStep)->toBe(0);
        expect($toStep)->toBe(1);
    });

    test('onComplete fires on finish', function (): void {
        $completed = false;
        $completedData = null;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
            ])
            ->data(['result' => 'success'])
            ->onComplete(function ($data) use (&$completed, &$completedData): void {
                $completed = true;
                $completedData = $data;
            });

        $widget->complete();

        expect($completed)->toBeTrue();
        expect($completedData)->toBe(['result' => 'success']);
    });

    test('onCancel fires on cancel', function (): void {
        $cancelled = false;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
            ])
            ->onCancel(function ($data) use (&$cancelled): void {
                $cancelled = true;
            });

        $widget->cancel();

        expect($cancelled)->toBeTrue();
    });

    test('step onEnter/onLeave fire correctly', function (): void {
        $entered = false;
        $left = false;

        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->onLeave(function ($data) use (&$left): void {
                        $left = true;
                    }),
                Step::make('second')
                    ->title('Second')
                    ->onEnter(function ($data) use (&$entered): void {
                        $entered = true;
                    }),
            ]);

        $widget->next();

        expect($left)->toBeTrue();
        expect($entered)->toBeTrue();
    });
});

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('shows current step content', function (): void {
        $widget = WizardWidget::make()
            ->title('Test Wizard')
            ->steps([
                Step::make('first')
                    ->title('First Step')
                    ->description('This is the first step'),
            ]);

        $step = $widget->getCurrentStep();

        expect($step->getTitle())->toBe('First Step');
        expect($step->getDescription())->toBe('This is the first step');
    });

    test('shows step indicator', function (): void {
        $widget = WizardWidget::make()
            ->title('Test Wizard')
            ->showStepIndicator(true)
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        expect($widget->showsStepIndicator())->toBeTrue();
    });

    test('shows correct buttons per step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        // First step - no back button
        expect($widget->canGoBack())->toBeFalse();
        expect($widget->canGoNext())->toBeTrue();
        expect($widget->isLastStep())->toBeFalse();

        $widget->next();

        // Last step - has back, shows finish
        expect($widget->canGoBack())->toBeTrue();
        expect($widget->isLastStep())->toBeTrue();
    });

    test('shows validation errors', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(fn($data) => 'Validation error message'),
            ]);

        $widget->validate();

        expect($widget->getStepError('first'))->toBe('Validation error message');
    });

    test('hidden steps not shown in indicator', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('hidden')->title('Hidden')->hidden(true),
                Step::make('third')->title('Third'),
            ]);

        $visibleSteps = $widget->getVisibleSteps();

        expect(count($visibleSteps))->toBe(2);
        expect(array_column($visibleSteps, 'id'))->not->toContain('hidden');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('single step wizard works', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('only')->title('Only Step'),
            ]);

        expect($widget->isFirstStep())->toBeTrue();
        expect($widget->isLastStep())->toBeTrue();
        expect($widget->canGoBack())->toBeFalse();
        expect($widget->canGoNext())->toBeFalse();
    });

    test('all optional steps can be skipped', function (): void {
        $widget = WizardWidget::make()
            ->allowSkip(true)
            ->steps([
                Step::make('required')->title('Required'),
                Step::make('optional1')->title('Optional 1')->optional(true),
                Step::make('optional2')->title('Optional 2')->optional(true),
            ]);

        // Can skip optional steps
        expect($widget->getStep('optional1')->isOptional())->toBeTrue();
        expect($widget->getStep('optional2')->isOptional())->toBeTrue();
    });

    test('hidden steps do not affect navigation', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('hidden')->title('Hidden')->hidden(true),
                Step::make('third')->title('Third'),
            ]);

        // Navigate past hidden step
        $widget->next();

        // Should skip hidden and go to third
        expect($widget->getCurrentStep()->getId())->toBe('third');
    });

    test('cancel confirmation works', function (): void {
        $widget = WizardWidget::make()
            ->confirmCancel(true)
            ->steps([
                Step::make('first')->title('First'),
            ]);

        expect($widget->requiresCancelConfirmation())->toBeTrue();
    });

    test('data persists across navigation', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->data(['initial' => 'value']);

        $widget->setData('step1_data', 'data1');
        $widget->next();
        $widget->setData('step2_data', 'data2');
        $widget->back();

        $data = $widget->getData();

        expect($data)->toHaveKey('initial');
        expect($data)->toHaveKey('step1_data');
        expect($data)->toHaveKey('step2_data');
    });
});

// =============================================================================
// CONFIGURATION TESTS
// =============================================================================

describe('configuration', function (): void {
    test('title can be set', function (): void {
        $widget = WizardWidget::make()
            ->title('My Wizard');

        expect($widget->getTitle())->toBe('My Wizard');
    });

    test('width can be set', function (): void {
        $widget = WizardWidget::make()
            ->width(80);

        expect($widget->getWidth())->toBe(80);
    });

    test('height can be set', function (): void {
        $widget = WizardWidget::make()
            ->height(24);

        expect($widget->getHeight())->toBe(24);
    });

    test('currentStep can be set by index', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->currentStep(1);

        expect($widget->getCurrentStepIndex())->toBe(1);
    });

    test('currentStep can be set by id', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->currentStep('second');

        expect($widget->getCurrentStep()->getId())->toBe('second');
    });

    test('initial data can be set', function (): void {
        $widget = WizardWidget::make()
            ->data(['key' => 'value']);

        expect($widget->getData())->toBe(['key' => 'value']);
    });
});

// =============================================================================
// NAVIGATION OPTIONS TESTS
// =============================================================================

describe('navigation options', function (): void {
    test('showStepIndicator can be toggled', function (): void {
        $widget = WizardWidget::make()
            ->showStepIndicator(false);

        expect($widget->showsStepIndicator())->toBeFalse();
    });

    test('showStepTitles can be toggled', function (): void {
        $widget = WizardWidget::make()
            ->showStepTitles(true);

        expect($widget->showsStepTitles())->toBeTrue();
    });

    test('allowBackNavigation can be disabled', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ])
            ->allowBackNavigation(false)
            ->currentStep(1);

        expect($widget->canGoBack())->toBeFalse();
    });

    test('allowSkip can be enabled', function (): void {
        $widget = WizardWidget::make()
            ->allowSkip(true);

        expect($widget->allowsSkip())->toBeTrue();
    });

    test('linear mode enforces step order', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
                Step::make('third')->title('Third'),
            ])
            ->linear(true);

        expect($widget->isLinear())->toBeTrue();

        // Cannot jump to step 3 from step 1
        $widget->goToStep(2);
        expect($widget->getCurrentStepIndex())->toBe(0);
    });
});

// =============================================================================
// BUTTON CONFIGURATION TESTS
// =============================================================================

describe('button configuration', function (): void {
    test('backButton label can be customized', function (): void {
        $widget = WizardWidget::make()
            ->backButton('Previous');

        expect($widget->getBackButtonLabel())->toBe('Previous');
    });

    test('nextButton label can be customized', function (): void {
        $widget = WizardWidget::make()
            ->nextButton('Continue');

        expect($widget->getNextButtonLabel())->toBe('Continue');
    });

    test('finishButton label can be customized', function (): void {
        $widget = WizardWidget::make()
            ->finishButton('Complete');

        expect($widget->getFinishButtonLabel())->toBe('Complete');
    });

    test('cancelButton label can be customized', function (): void {
        $widget = WizardWidget::make()
            ->cancelButton('Exit');

        expect($widget->getCancelButtonLabel())->toBe('Exit');
    });

    test('showCancel can be toggled', function (): void {
        $widget = WizardWidget::make()
            ->showCancel(false);

        expect($widget->showsCancel())->toBeFalse();
    });
});

// =============================================================================
// STEP TESTS
// =============================================================================

describe('step', function (): void {
    test('step can have title', function (): void {
        $step = Step::make('test')
            ->title('Test Title');

        expect($step->getTitle())->toBe('Test Title');
    });

    test('step can have description', function (): void {
        $step = Step::make('test')
            ->description('Test description');

        expect($step->getDescription())->toBe('Test description');
    });

    test('step can have component', function (): void {
        $component = fn($data) => null;

        $step = Step::make('test')
            ->component($component);

        expect($step->hasComponent())->toBeTrue();
    });

    test('step can have validate function', function (): void {
        $step = Step::make('test')
            ->validate(fn($data) => true);

        expect($step->hasValidation())->toBeTrue();
    });

    test('step can be optional', function (): void {
        $step = Step::make('test')
            ->optional(true);

        expect($step->isOptional())->toBeTrue();
    });

    test('step can be hidden', function (): void {
        $step = Step::make('test')
            ->hidden(true);

        expect($step->isHidden())->toBeTrue();
    });

    test('step hidden can be conditional', function (): void {
        $step = Step::make('test')
            ->hidden(fn() => true);

        expect($step->isHidden())->toBeTrue();
    });
});

// =============================================================================
// METHODS TESTS
// =============================================================================

describe('methods', function (): void {
    test('getCurrentStep returns current step', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        expect($widget->getCurrentStep()->getId())->toBe('first');
    });

    test('getSteps returns all steps', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        expect(count($widget->getSteps()))->toBe(2);
    });

    test('getStep returns specific step by id', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        $step = $widget->getStep('second');

        expect($step)->not->toBeNull();
        expect($step->getId())->toBe('second');
    });

    test('isStepComplete returns correct state', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')->title('First'),
                Step::make('second')->title('Second'),
            ]);

        $widget->next();

        expect($widget->isStepComplete('first'))->toBeTrue();
        expect($widget->isStepComplete('second'))->toBeFalse();
    });

    test('isStepValid returns correct state', function (): void {
        $widget = WizardWidget::make()
            ->steps([
                Step::make('first')
                    ->title('First')
                    ->validate(fn($data) => isset($data['name'])),
            ])
            ->data(['name' => 'Test']);

        expect($widget->isStepValid('first'))->toBeTrue();
    });

    test('mergeData combines data', function (): void {
        $widget = WizardWidget::make()
            ->data(['key1' => 'value1']);

        $widget->mergeData(['key2' => 'value2', 'key3' => 'value3']);

        $data = $widget->getData();

        expect($data)->toHaveKey('key1');
        expect($data)->toHaveKey('key2');
        expect($data)->toHaveKey('key3');
    });

    test('clearData removes all data', function (): void {
        $widget = WizardWidget::make()
            ->data(['key1' => 'value1', 'key2' => 'value2']);

        $widget->clearData();

        expect($widget->getData())->toBe([]);
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = WizardWidget::make();

        $result = $widget
            ->title('Test')
            ->width(80)
            ->height(24)
            ->showStepIndicator(true)
            ->showStepTitles(true)
            ->allowBackNavigation(true);

        expect($result)->toBeInstanceOf(WizardWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = WizardWidget::make()
            ->title('New Transform')
            ->steps([
                Step::make('source')
                    ->title('Source')
                    ->description('Select the source table')
                    ->validate(fn($data) => isset($data['source_table']) ?: 'Please select a source'),

                Step::make('name')
                    ->title('Name')
                    ->description('Name your transform')
                    ->validate(fn($data) => strlen($data['name'] ?? '') >= 3 ?: 'Name must be at least 3 characters'),

                Step::make('filter')
                    ->title('Filter')
                    ->description('Optionally filter the source data')
                    ->optional(true),
            ])
            ->showStepIndicator(true)
            ->showStepTitles(true)
            ->allowBackNavigation(true)
            ->backButton('Previous')
            ->nextButton('Continue')
            ->finishButton('Create Transform')
            ->onComplete(fn($data) => null);

        expect($widget->getTitle())->toBe('New Transform');
        expect(count($widget->getSteps()))->toBe(3);
        expect($widget->getStep('filter')->isOptional())->toBeTrue();
    });
});
