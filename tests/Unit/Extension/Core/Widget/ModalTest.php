<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\ModalWidget;
use Crumbls\Tui\Extension\Core\Widget\Modal\Button;

/**
 * Modal Component Tests
 *
 * An overlay dialog component that appears on top of the current screen,
 * trapping focus until dismissed.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders title in header', function (): void {
        $widget = ModalWidget::make()
            ->title('Delete Transform?')
            ->body('Are you sure?')
            ->open();

        $lines = renderToLines($widget, 60, 10);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Delete Transform?');
    });

    test('renders body content', function (): void {
        $widget = ModalWidget::make()
            ->title('Test Modal')
            ->body('This is the body content')
            ->open();

        $lines = renderToLines($widget, 60, 10);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('This is the body content');
    });

    test('renders buttons in footer', function (): void {
        $widget = ModalWidget::make()
            ->title('Confirm')
            ->body('Continue?')
            ->buttons('ok-cancel')
            ->open();

        $lines = renderToLines($widget, 60, 10);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('OK');
        expect($fullOutput)->toContain('Cancel');
    });

    test('centers modal on screen', function (): void {
        $widget = ModalWidget::make()
            ->title('Centered')
            ->body('Content')
            ->centered(true)
            ->open();

        expect($widget->isCentered())->toBeTrue();
    });

    test('respects width constraints', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->body('Content')
            ->width(40);

        expect($widget->getWidth())->toBe(40);
    });

    test('respects height constraints', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->body('Content')
            ->height(15);

        expect($widget->getHeight())->toBe(15);
    });

    test('shows overlay when enabled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->overlay(true);

        expect($widget->hasOverlay())->toBeTrue();
    });
});

// =============================================================================
// INTERACTION TESTS
// =============================================================================

describe('interaction', function (): void {
    test('enter clicks focused button', function (): void {
        $clicked = false;

        $widget = ModalWidget::make()
            ->title('Test')
            ->body('Content')
            ->buttons([
                Button::make('ok')->label('OK'),
            ])
            ->onButtonClick(function (string $buttonId) use (&$clicked): void {
                $clicked = true;
            })
            ->open();

        $widget->clickFocusedButton();

        expect($clicked)->toBeTrue();
    });

    test('escape triggers cancel', function (): void {
        $cancelled = false;

        $widget = ModalWidget::make()
            ->title('Test')
            ->closeOnEscape(true)
            ->onCancel(function () use (&$cancelled): void {
                $cancelled = true;
            })
            ->open();

        $widget->cancel();

        expect($cancelled)->toBeTrue();
    });

    test('tab navigates buttons forward', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('cancel')->label('Cancel'),
                Button::make('ok')->label('OK'),
            ])
            ->open();

        expect($widget->getFocusedButtonIndex())->toBe(0);

        $widget->focusNextButton();

        expect($widget->getFocusedButtonIndex())->toBe(1);
    });

    test('arrow keys navigate buttons', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('cancel')->label('Cancel'),
                Button::make('ok')->label('OK'),
            ])
            ->open();

        $widget->focusNextButton();
        expect($widget->getFocusedButtonIndex())->toBe(1);

        $widget->focusPreviousButton();
        expect($widget->getFocusedButtonIndex())->toBe(0);
    });

    test('focus trapped inside modal', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->trapFocus(true)
            ->buttons([
                Button::make('cancel')->label('Cancel'),
                Button::make('ok')->label('OK'),
            ])
            ->open();

        expect($widget->isFocusTrapped())->toBeTrue();

        // At last button, wraps to first
        $widget->focusNextButton();
        $widget->focusNextButton();

        expect($widget->getFocusedButtonIndex())->toBe(0);
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onConfirm fires for primary button', function (): void {
        $confirmed = false;

        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok-cancel')
            ->onConfirm(function () use (&$confirmed): void {
                $confirmed = true;
            })
            ->open();

        $widget->confirm();

        expect($confirmed)->toBeTrue();
    });

    test('onCancel fires for cancel or escape', function (): void {
        $cancelled = false;

        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok-cancel')
            ->onCancel(function () use (&$cancelled): void {
                $cancelled = true;
            })
            ->open();

        $widget->cancel();

        expect($cancelled)->toBeTrue();
    });

    test('onClose fires with button id', function (): void {
        $closedWith = null;

        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('delete')->label('Delete'),
            ])
            ->onClose(function (string $buttonId) use (&$closedWith): void {
                $closedWith = $buttonId;
            })
            ->open();

        $widget->close('delete');

        expect($closedWith)->toBe('delete');
    });

    test('onOpen fires when opened', function (): void {
        $opened = false;

        $widget = ModalWidget::make()
            ->title('Test')
            ->onOpen(function () use (&$opened): void {
                $opened = true;
            });

        $widget->open();

        expect($opened)->toBeTrue();
    });

    test('onButtonClick fires with button id', function (): void {
        $clickedButton = null;

        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('custom')->label('Custom'),
            ])
            ->onButtonClick(function (string $buttonId) use (&$clickedButton): void {
                $clickedButton = $buttonId;
            })
            ->open();

        $widget->clickButton('custom');

        expect($clickedButton)->toBe('custom');
    });
});

// =============================================================================
// STATIC HELPER TESTS
// =============================================================================

describe('static helpers', function (): void {
    test('confirm creates yes-no modal', function (): void {
        $widget = ModalWidget::confirmation('Delete this item?', fn() => null);

        $buttons = $widget->getButtons();
        $buttonIds = array_map(fn(Button $b) => $b->getId(), $buttons);

        expect($buttonIds)->toContain('yes');
        expect($buttonIds)->toContain('no');
    });

    test('alert creates ok modal', function (): void {
        $widget = ModalWidget::alert('Operation completed');

        $buttons = $widget->getButtons();
        $buttonIds = array_map(fn(Button $b) => $b->getId(), $buttons);

        expect(count($buttonIds))->toBe(1);
        expect($buttonIds)->toContain('ok');
    });

    test('error creates error-styled modal', function (): void {
        $widget = ModalWidget::error('Something went wrong', 'Details here');

        expect($widget->getTitle())->toBe('Something went wrong');
        expect($widget->getBody())->toBe('Details here');
    });

    test('prompt creates input modal', function (): void {
        $widget = ModalWidget::prompt('Enter name:', fn($name) => null);

        expect($widget->getTitle())->toBe('Enter name:');
        expect($widget->hasInput())->toBeTrue();
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty body renders correctly', function (): void {
        $widget = ModalWidget::make()
            ->title('Empty Modal')
            ->body('')
            ->open();

        expect($widget->getBody())->toBe('');
    });

    test('very long content is handled', function (): void {
        $longContent = str_repeat('a', 500);
        $widget = ModalWidget::make()
            ->title('Long Content')
            ->body($longContent)
            ->maxWidth(50);

        expect($widget->getBody())->toBe($longContent);
    });

    test('modal respects max width constraints', function (): void {
        $widget = ModalWidget::make()
            ->title('Constrained')
            ->maxWidth(60)
            ->maxHeight(20);

        expect($widget->getMaxWidth())->toBe(60);
        expect($widget->getMaxHeight())->toBe(20);
    });

    test('modal respects min constraints', function (): void {
        $widget = ModalWidget::make()
            ->title('Min')
            ->minWidth(30)
            ->minHeight(10);

        expect($widget->getMinWidth())->toBe(30);
        expect($widget->getMinHeight())->toBe(10);
    });

    test('close restores state', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->open();

        expect($widget->isOpen())->toBeTrue();

        $widget->close();

        expect($widget->isOpen())->toBeFalse();
    });
});

// =============================================================================
// BUTTON TESTS
// =============================================================================

describe('buttons', function (): void {
    test('preset ok buttons work', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok');

        $buttons = $widget->getButtons();
        expect(count($buttons))->toBe(1);
    });

    test('preset ok-cancel buttons work', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok-cancel');

        $buttons = $widget->getButtons();
        expect(count($buttons))->toBe(2);
    });

    test('preset yes-no buttons work', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('yes-no');

        $buttons = $widget->getButtons();
        expect(count($buttons))->toBe(2);
    });

    test('preset yes-no-cancel buttons work', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('yes-no-cancel');

        $buttons = $widget->getButtons();
        expect(count($buttons))->toBe(3);
    });

    test('custom buttons array work', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('cancel')->label('Cancel')->style('secondary'),
                Button::make('delete')->label('Delete')->style('danger'),
            ]);

        $buttons = $widget->getButtons();
        expect(count($buttons))->toBe(2);
    });

    test('confirmButton customizes primary button', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok-cancel')
            ->confirmButton('Save', 'primary');

        $buttons = $widget->getButtons();
        $confirmButton = null;
        foreach ($buttons as $button) {
            if ($button->getId() === 'ok') {
                $confirmButton = $button;
                break;
            }
        }

        expect($confirmButton)->not->toBeNull();
        expect($confirmButton->getLabel())->toBe('Save');
    });

    test('cancelButton customizes cancel button', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons('ok-cancel')
            ->cancelButton('Dismiss', 'secondary');

        $buttons = $widget->getButtons();
        $cancelButton = null;
        foreach ($buttons as $button) {
            if ($button->getId() === 'cancel') {
                $cancelButton = $button;
                break;
            }
        }

        expect($cancelButton)->not->toBeNull();
        expect($cancelButton->getLabel())->toBe('Dismiss');
    });

    test('focusNextButton cycles through buttons', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('a')->label('A'),
                Button::make('b')->label('B'),
                Button::make('c')->label('C'),
            ])
            ->open();

        expect($widget->getFocusedButtonIndex())->toBe(0);

        $widget->focusNextButton();
        expect($widget->getFocusedButtonIndex())->toBe(1);

        $widget->focusNextButton();
        expect($widget->getFocusedButtonIndex())->toBe(2);

        $widget->focusNextButton();
        expect($widget->getFocusedButtonIndex())->toBe(0);
    });

    test('focusPreviousButton cycles through buttons', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('a')->label('A'),
                Button::make('b')->label('B'),
                Button::make('c')->label('C'),
            ])
            ->open();

        expect($widget->getFocusedButtonIndex())->toBe(0);

        $widget->focusPreviousButton();
        expect($widget->getFocusedButtonIndex())->toBe(2);

        $widget->focusPreviousButton();
        expect($widget->getFocusedButtonIndex())->toBe(1);
    });

    test('getButtons returns all buttons', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('a')->label('A'),
                Button::make('b')->label('B'),
            ]);

        expect(count($widget->getButtons()))->toBe(2);
    });

    test('clickButton triggers specific button', function (): void {
        $clicked = null;

        $widget = ModalWidget::make()
            ->title('Test')
            ->buttons([
                Button::make('first')->label('First'),
                Button::make('second')->label('Second'),
            ])
            ->onButtonClick(function (string $id) use (&$clicked): void {
                $clicked = $id;
            })
            ->open();

        $widget->clickButton('second');

        expect($clicked)->toBe('second');
    });
});

// =============================================================================
// CONTROL TESTS
// =============================================================================

describe('control', function (): void {
    test('open opens the modal', function (): void {
        $widget = ModalWidget::make()
            ->title('Test');

        expect($widget->isOpen())->toBeFalse();

        $widget->open();

        expect($widget->isOpen())->toBeTrue();
    });

    test('close closes the modal', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->open();

        expect($widget->isOpen())->toBeTrue();

        $widget->close();

        expect($widget->isOpen())->toBeFalse();
    });

    test('isOpen returns correct state', function (): void {
        $widget = ModalWidget::make()
            ->title('Test');

        expect($widget->isOpen())->toBeFalse();

        $widget->open();
        expect($widget->isOpen())->toBeTrue();

        $widget->close();
        expect($widget->isOpen())->toBeFalse();
    });
});

// =============================================================================
// CONTENT TESTS
// =============================================================================

describe('content', function (): void {
    test('getBody returns body content', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->body('Body text');

        expect($widget->getBody())->toBe('Body text');
    });

    test('setBody updates body content', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->body('Initial');

        $widget->setBody('Updated');

        expect($widget->getBody())->toBe('Updated');
    });

    test('getTitle returns title', function (): void {
        $widget = ModalWidget::make()
            ->title('Modal Title');

        expect($widget->getTitle())->toBe('Modal Title');
    });

    test('setTitle updates title', function (): void {
        $widget = ModalWidget::make()
            ->title('Initial');

        $widget->setTitle('Updated');

        expect($widget->getTitle())->toBe('Updated');
    });

    test('footer can be set', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->footer('Footer content');

        expect($widget->getFooter())->toBe('Footer content');
    });
});

// =============================================================================
// BEHAVIOR TESTS
// =============================================================================

describe('behavior', function (): void {
    test('closeOnEscape can be enabled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->closeOnEscape(true);

        expect($widget->closesOnEscape())->toBeTrue();
    });

    test('closeOnClickOutside can be enabled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->closeOnClickOutside(true);

        expect($widget->closesOnClickOutside())->toBeTrue();
    });

    test('trapFocus can be enabled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->trapFocus(true);

        expect($widget->isFocusTrapped())->toBeTrue();
    });

    test('focusFirst can target specific component', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->focusFirst('input-name');

        expect($widget->getFocusFirst())->toBe('input-name');
    });
});

// =============================================================================
// DISPLAY OPTIONS TESTS
// =============================================================================

describe('display options', function (): void {
    test('centered can be enabled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->centered(true);

        expect($widget->isCentered())->toBeTrue();
    });

    test('position can be set', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->position(10, 5);

        expect($widget->getPositionX())->toBe(10);
        expect($widget->getPositionY())->toBe(5);
    });

    test('overlay can be toggled', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->overlay(true);

        expect($widget->hasOverlay())->toBeTrue();

        $widget->overlay(false);

        expect($widget->hasOverlay())->toBeFalse();
    });

    test('overlayStyle can be set', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->overlayStyle('dim');

        expect($widget->getOverlayStyle())->toBe('dim');

        $widget->overlayStyle('blur');

        expect($widget->getOverlayStyle())->toBe('blur');
    });

    test('fitContent auto-sizes to content', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->fitContent(true);

        expect($widget->isFitContent())->toBeTrue();
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are set', function (): void {
        $widget = ModalWidget::make()
            ->title('Test');

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('enter');
        expect($bindings)->toHaveKey('escape');
        expect($bindings)->toHaveKey('tab');
        expect($bindings)->toHaveKey('left');
        expect($bindings)->toHaveKey('right');
    });

    test('custom key bindings can be set', function (): void {
        $widget = ModalWidget::make()
            ->title('Test')
            ->keyBindings([
                'space' => 'clickFocusedButton',
            ]);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('space');
        expect($bindings['space'])->toBe('clickFocusedButton');
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = ModalWidget::make();

        $result = $widget
            ->title('Test')
            ->body('Content')
            ->footer('Footer')
            ->buttons('ok-cancel')
            ->width(50)
            ->height(20)
            ->centered(true);

        expect($result)->toBeInstanceOf(ModalWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = ModalWidget::make()
            ->title('Delete Transform?')
            ->body("Are you sure you want to delete this?\n\nThis cannot be undone.")
            ->buttons('yes-no')
            ->confirmButton('Delete', 'danger')
            ->cancelButton('Keep', 'secondary')
            ->width(50)
            ->centered(true)
            ->closeOnEscape(true)
            ->overlay(true);

        expect($widget->getTitle())->toBe('Delete Transform?');
        expect($widget->getWidth())->toBe(50);
        expect($widget->isCentered())->toBeTrue();
        expect($widget->closesOnEscape())->toBeTrue();
        expect($widget->hasOverlay())->toBeTrue();
    });
});
