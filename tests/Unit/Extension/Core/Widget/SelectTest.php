<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\SelectWidget;

/**
 * Select Component Tests
 *
 * A dropdown/picker component for choosing from a list of options.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders label when provided', function (): void {
        $widget = SelectWidget::make()
            ->label('Column')
            ->options(['post_type', 'post_status', 'post_author']);

        $lines = renderToLines($widget, 40, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Column');
    });

    test('renders selected value or placeholder', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->placeholder('Select an option...');

        $lines = renderToLines($widget, 40, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Select an option...');
    });

    test('renders dropdown indicator', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2'])
            ->indicator("\u{25BC}");

        $lines = renderToLines($widget, 40, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain("\u{25BC}");
    });

    test('opens dropdown on activation', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3']);

        expect($widget->isOpen())->toBeFalse();

        $widget->open();

        expect($widget->isOpen())->toBeTrue();
    });

    test('renders all visible options when open', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open();

        $lines = renderToLines($widget, 40, 10);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('option1');
        expect($fullOutput)->toContain('option2');
        expect($fullOutput)->toContain('option3');
    });

    test('highlights correct option', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open()
            ->highlightOption(1);

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('shows scroll indicator when needed', function (): void {
        $options = [];
        for ($i = 1; $i <= 20; $i++) {
            $options[] = "option{$i}";
        }

        $widget = SelectWidget::make()
            ->options($options)
            ->maxHeight(5)
            ->open();

        // The widget should track that scrolling is needed
        expect(count($widget->getOptions()))->toBeGreaterThan($widget->getMaxHeight());
    });
});

// =============================================================================
// SELECTION TESTS
// =============================================================================

describe('selection', function (): void {
    test('selecting option updates value', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->open()
            ->highlightOption(1);

        $widget->selectHighlighted();

        expect($widget->getValue())->toBe('publish');
    });

    test('selecting option closes dropdown', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->open()
            ->highlightOption(0);

        $widget->selectHighlighted();

        expect($widget->isOpen())->toBeFalse();
    });

    test('getValue returns correct value', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->value('publish');

        expect($widget->getValue())->toBe('publish');
    });

    test('getLabel returns correct label', function (): void {
        $widget = SelectWidget::make()
            ->options([
                'eq' => 'Equals (=)',
                'neq' => 'Not Equals (!=)',
            ])
            ->value('eq');

        expect($widget->getLabel())->toBe('Equals (=)');
    });

    test('clear resets to placeholder', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->value('publish')
            ->placeholder('Select...');

        $widget->clear();

        expect($widget->getValue())->toBeNull();
    });
});

// =============================================================================
// NAVIGATION TESTS
// =============================================================================

describe('navigation', function (): void {
    test('arrow keys move highlight down', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open();

        expect($widget->getHighlightedIndex())->toBe(0);

        $widget->nextOption();

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('arrow keys move highlight up', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open()
            ->highlightOption(2);

        $widget->previousOption();

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('enter selects highlighted option', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open()
            ->highlightOption(1);

        $widget->selectHighlighted();

        expect($widget->getValue())->toBe('option2');
    });

    test('escape closes without selecting', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->value('option1')
            ->open()
            ->highlightOption(2);

        $widget->close();

        expect($widget->isOpen())->toBeFalse();
        expect($widget->getValue())->toBe('option1');
    });

    test('wraps or stops at boundaries going down', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open()
            ->highlightOption(2);

        $widget->nextOption();

        // Should either wrap to 0 or stay at 2
        expect($widget->getHighlightedIndex())->toBeIn([0, 2]);
    });

    test('wraps or stops at boundaries going up', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3'])
            ->open()
            ->highlightOption(0);

        $widget->previousOption();

        // Should either wrap to 2 or stay at 0
        expect($widget->getHighlightedIndex())->toBeIn([0, 2]);
    });

    test('firstOption jumps to first', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3', 'option4', 'option5'])
            ->open()
            ->highlightOption(3);

        $widget->firstOption();

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('lastOption jumps to last', function (): void {
        $widget = SelectWidget::make()
            ->options(['option1', 'option2', 'option3', 'option4', 'option5'])
            ->open()
            ->highlightOption(1);

        $widget->lastOption();

        expect($widget->getHighlightedIndex())->toBe(4);
    });
});

// =============================================================================
// SEARCH TESTS
// =============================================================================

describe('search', function (): void {
    test('typing filters options', function (): void {
        $widget = SelectWidget::make()
            ->options(['post_type', 'post_status', 'author', 'date'])
            ->searchable(true)
            ->open();

        $widget->search('post');

        $filtered = $widget->getFilteredOptions();
        expect(count($filtered))->toBe(2);
    });

    test('search is case-insensitive', function (): void {
        $widget = SelectWidget::make()
            ->options(['Post_Type', 'POST_STATUS', 'author'])
            ->searchable(true)
            ->open();

        $widget->search('post');

        $filtered = $widget->getFilteredOptions();
        expect(count($filtered))->toBe(2);
    });

    test('clearing search shows all options', function (): void {
        $widget = SelectWidget::make()
            ->options(['post_type', 'post_status', 'author', 'date'])
            ->searchable(true)
            ->open();

        $widget->search('post');
        expect(count($widget->getFilteredOptions()))->toBe(2);

        $widget->clearSearch();

        expect(count($widget->getFilteredOptions()))->toBe(4);
    });

    test('no results shows empty state', function (): void {
        $widget = SelectWidget::make()
            ->options(['post_type', 'post_status', 'author'])
            ->searchable(true)
            ->noResultsText('No matches found')
            ->open();

        $widget->search('xyz');

        $filtered = $widget->getFilteredOptions();
        expect(count($filtered))->toBe(0);
    });

    test('search matches partial strings', function (): void {
        $widget = SelectWidget::make()
            ->options(['post_type', 'status', 'author_id'])
            ->searchable(true)
            ->open();

        $widget->search('ost');

        $filtered = $widget->getFilteredOptions();
        expect(count($filtered))->toBe(1);
        expect(array_values($filtered)[0])->toBe('post_type');
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onChange fires on selection', function (): void {
        $called = false;
        $receivedValue = null;
        $receivedLabel = null;

        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending'])
            ->onChange(function (mixed $value, string $label) use (&$called, &$receivedValue, &$receivedLabel): void {
                $called = true;
                $receivedValue = $value;
                $receivedLabel = $label;
            })
            ->open()
            ->highlightOption(1);

        $widget->selectHighlighted();

        expect($called)->toBeTrue();
        expect($receivedValue)->toBe('publish');
        expect($receivedLabel)->toBe('publish');
    });

    test('onOpen fires when dropdown opens', function (): void {
        $called = false;

        $widget = SelectWidget::make()
            ->options(['option1', 'option2'])
            ->onOpen(function () use (&$called): void {
                $called = true;
            });

        $widget->open();

        expect($called)->toBeTrue();
    });

    test('onClose fires when dropdown closes', function (): void {
        $called = false;

        $widget = SelectWidget::make()
            ->options(['option1', 'option2'])
            ->onClose(function () use (&$called): void {
                $called = true;
            })
            ->open();

        $widget->close();

        expect($called)->toBeTrue();
    });

    test('onSearch fires on search input', function (): void {
        $called = false;
        $receivedQuery = '';

        $widget = SelectWidget::make()
            ->options(['option1', 'option2'])
            ->searchable(true)
            ->onSearch(function (string $query) use (&$called, &$receivedQuery): void {
                $called = true;
                $receivedQuery = $query;
            })
            ->open();

        $widget->search('opt');

        expect($called)->toBeTrue();
        expect($receivedQuery)->toBe('opt');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty options shows empty state', function (): void {
        $widget = SelectWidget::make()
            ->options([]);

        expect($widget->getOptions())->toBe([]);
    });

    test('single option can be selected', function (): void {
        $widget = SelectWidget::make()
            ->options(['only_option'])
            ->open();

        $widget->selectHighlighted();

        expect($widget->getValue())->toBe('only_option');
    });

    test('very long option labels are handled', function (): void {
        $longLabel = str_repeat('a', 100);
        $widget = SelectWidget::make()
            ->options([$longLabel, 'short'])
            ->width(20);

        expect($widget->getOptions())->toContain($longLabel);
    });

    test('many options scroll correctly', function (): void {
        $options = [];
        for ($i = 1; $i <= 50; $i++) {
            $options[] = "option{$i}";
        }

        $widget = SelectWidget::make()
            ->options($options)
            ->maxHeight(5)
            ->open()
            ->highlightOption(25);

        expect($widget->getHighlightedIndex())->toBe(25);
        expect($widget->getScrollOffset())->toBeGreaterThan(0);
    });

    test('grouped options render with headers', function (): void {
        $widget = SelectWidget::make()
            ->optionGroups([
                'Text Columns' => ['title', 'content'],
                'Date Columns' => ['created_at', 'updated_at'],
            ]);

        $options = $widget->getOptions();
        expect(count($options))->toBe(4);
    });
});

// =============================================================================
// OPTIONS TESTS
// =============================================================================

describe('options', function (): void {
    test('simple array of values works', function (): void {
        $widget = SelectWidget::make()
            ->options(['draft', 'publish', 'pending']);

        $options = $widget->getOptions();

        expect(count($options))->toBe(3);
        expect($options)->toContain('draft');
    });

    test('associative array with value => label works', function (): void {
        $widget = SelectWidget::make()
            ->options([
                'eq' => 'Equals (=)',
                'neq' => 'Not Equals (!=)',
            ]);

        expect($widget->getOptions())->toHaveKey('eq');
        expect($widget->getOptions()['eq'])->toBe('Equals (=)');
    });

    test('getOptions returns all options', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b', 'c']);

        expect(count($widget->getOptions()))->toBe(3);
    });

    test('setOptions updates options', function (): void {
        $widget = SelectWidget::make()
            ->options(['old1', 'old2']);

        $widget->setOptions(['new1', 'new2', 'new3']);

        expect(count($widget->getOptions()))->toBe(3);
    });

    test('addOption adds single option', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b']);

        $widget->addOption('c');

        expect(count($widget->getOptions()))->toBe(3);
        expect($widget->hasOption('c'))->toBeTrue();
    });

    test('removeOption removes option', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b', 'c']);

        $widget->removeOption('b');

        expect(count($widget->getOptions()))->toBe(2);
        expect($widget->hasOption('b'))->toBeFalse();
    });

    test('hasOption checks option existence', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b', 'c']);

        expect($widget->hasOption('a'))->toBeTrue();
        expect($widget->hasOption('z'))->toBeFalse();
    });
});

// =============================================================================
// DROPDOWN CONTROL TESTS
// =============================================================================

describe('dropdown control', function (): void {
    test('open opens the dropdown', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b']);

        $widget->open();

        expect($widget->isOpen())->toBeTrue();
    });

    test('close closes the dropdown', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b'])
            ->open();

        $widget->close();

        expect($widget->isOpen())->toBeFalse();
    });

    test('toggle switches dropdown state', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b']);

        expect($widget->isOpen())->toBeFalse();

        $widget->toggle();
        expect($widget->isOpen())->toBeTrue();

        $widget->toggle();
        expect($widget->isOpen())->toBeFalse();
    });

    test('isOpen returns correct state', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b']);

        expect($widget->isOpen())->toBeFalse();

        $widget->open();

        expect($widget->isOpen())->toBeTrue();
    });
});

// =============================================================================
// CONFIGURATION TESTS
// =============================================================================

describe('configuration', function (): void {
    test('label can be set', function (): void {
        $widget = SelectWidget::make()
            ->label('Column');

        expect($widget->getLabel())->toBe('Column');
    });

    test('value can be set', function (): void {
        $widget = SelectWidget::make()
            ->options(['a', 'b', 'c'])
            ->value('b');

        expect($widget->getValue())->toBe('b');
    });

    test('placeholder can be set', function (): void {
        $widget = SelectWidget::make()
            ->placeholder('Select...');

        expect($widget->getPlaceholder())->toBe('Select...');
    });

    test('disabled can be set', function (): void {
        $widget = SelectWidget::make()
            ->disabled(true);

        expect($widget->isDisabled())->toBeTrue();
    });

    test('required can be set', function (): void {
        $widget = SelectWidget::make()
            ->required(true);

        expect($widget->isRequired())->toBeTrue();
    });

    test('width can be set', function (): void {
        $widget = SelectWidget::make()
            ->width(25);

        expect($widget->getWidth())->toBe(25);
    });

    test('maxHeight can be set', function (): void {
        $widget = SelectWidget::make()
            ->maxHeight(8);

        expect($widget->getMaxHeight())->toBe(8);
    });

    test('searchable can be enabled', function (): void {
        $widget = SelectWidget::make()
            ->searchable(true);

        expect($widget->isSearchable())->toBeTrue();
    });

    test('searchPlaceholder can be set', function (): void {
        $widget = SelectWidget::make()
            ->searchPlaceholder('Type to filter...');

        expect($widget->getSearchPlaceholder())->toBe('Type to filter...');
    });

    test('noResultsText can be set', function (): void {
        $widget = SelectWidget::make()
            ->noResultsText('No matches');

        expect($widget->getNoResultsText())->toBe('No matches');
    });

    test('indicator can be customized', function (): void {
        $widget = SelectWidget::make()
            ->indicator('>');

        expect($widget->getIndicator())->toBe('>');
    });

    test('labelWidth can be set', function (): void {
        $widget = SelectWidget::make()
            ->labelWidth(15);

        expect($widget->getLabelWidth())->toBe(15);
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are set', function (): void {
        $widget = SelectWidget::make();
        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('enter');
        expect($bindings)->toHaveKey('escape');
        expect($bindings)->toHaveKey('up');
        expect($bindings)->toHaveKey('down');
    });

    test('custom key bindings can be set', function (): void {
        $widget = SelectWidget::make()
            ->keyBindings([
                'space' => 'toggleOrSelect',
                'tab' => 'selectAndNext',
            ]);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('space');
        expect($bindings['space'])->toBe('toggleOrSelect');
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = SelectWidget::make();

        $result = $widget
            ->label('Test')
            ->options(['a', 'b'])
            ->value('a')
            ->placeholder('Select...')
            ->width(30)
            ->maxHeight(10)
            ->searchable(true);

        expect($result)->toBeInstanceOf(SelectWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = SelectWidget::make()
            ->label('Column')
            ->labelWidth(12)
            ->options(['post_type', 'post_status', 'post_author'])
            ->value('post_type')
            ->placeholder('Select a column...')
            ->searchable(true)
            ->width(25)
            ->maxHeight(8);

        expect($widget->getValue())->toBe('post_type');
        expect($widget->getWidth())->toBe(25);
        expect($widget->getMaxHeight())->toBe(8);
        expect($widget->isSearchable())->toBeTrue();
    });
});
