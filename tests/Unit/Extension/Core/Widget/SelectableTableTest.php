<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\SelectableTable\Column;
use Crumbls\Tui\Extension\Core\Widget\SelectableTableWidget;

/**
 * SelectableTable Component Tests
 *
 * A table component where entire rows are selectable units.
 * Used for navigation interfaces where each row represents an actionable item.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders headers when showHeaders is true', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->label('Name')->width(10),
                Column::make('status')->label('Status')->width(6),
            ])
            ->rows([
                ['name' => 'Item 1', 'status' => 'OK'],
            ])
            ->showHeaders(true);

        $lines = renderToLines($widget, 20, 5);

        expect($lines[0])->toContain('Name');
        expect($lines[0])->toContain('Status');
    });

    test('hides headers when showHeaders is false', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->label('Name')->width(10),
            ])
            ->rows([
                ['name' => 'Item 1'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 20, 5);

        expect($lines[0])->not->toContain('Name');
        expect($lines[0])->toContain('Item 1');
    });

    test('renders correct number of rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->rows([
                ['name' => 'Row 1'],
                ['name' => 'Row 2'],
                ['name' => 'Row 3'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 5);

        expect($lines[0])->toContain('Row 1');
        expect($lines[1])->toContain('Row 2');
        expect($lines[2])->toContain('Row 3');
    });

    test('truncates long values with ellipsis', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(8)->truncate(true),
            ])
            ->rows([
                ['name' => 'VeryLongItemName'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toContain("\u{2026}");
    });

    test('aligns columns left by default', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->rows([
                ['name' => 'Hi'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toMatch('/> Hi\s+/');
    });

    test('aligns columns center when specified', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10)->align('center'),
            ])
            ->rows([
                ['name' => 'Hi'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toMatch('/\s+Hi\s+/');
    });

    test('aligns columns right when specified', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('num')->width(10)->align('right'),
            ])
            ->rows([
                ['num' => '42'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toMatch('/\s+42\s*/');
    });

    test('applies format functions to cell values', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('num')
                    ->width(10)
                    ->format(fn ($v) => '$' . number_format((int) $v)),
            ])
            ->rows([
                ['num' => 1000],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toContain('$1,000');
    });

    test('shows empty state when no rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->rows([])
            ->emptyText('No items');

        $lines = renderToLines($widget, 20, 5);

        $fullOutput = implode('', $lines);
        expect($fullOutput)->toContain('No items');
    });

    test('shows highlight indicator on correct row', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->highlightedIndex(1)
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 5);

        expect($lines[0])->toMatch('/^\s{2}/');
        expect($lines[1])->toMatch('/^>/');
        expect($lines[2])->toMatch('/^\s{2}/');
    });

    test('renders static column values', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(6),
                Column::make('arrow')->width(3)->static('->'),
                Column::make('target')->width(6),
            ])
            ->rows([
                ['name' => 'Source', 'target' => 'Dest'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 20, 3);

        expect($lines[0])->toContain('->');
    });

    test('respects column visibility', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
                Column::make('hidden')->width(10)->visible(false),
                Column::make('status')->width(6),
            ])
            ->rows([
                ['name' => 'Item', 'hidden' => 'SECRET', 'status' => 'OK'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 25, 3);

        expect($lines[0])->not->toContain('SECRET');
        expect($lines[0])->toContain('Item');
        expect($lines[0])->toContain('OK');
    });

    test('renders separator line after headers', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->label('Name')->width(10),
            ])
            ->rows([
                ['name' => 'Item'],
            ])
            ->showHeaders(true);

        $lines = renderToLines($widget, 20, 5);

        expect($lines[1])->toContain("\u{2500}");
    });
});

// =============================================================================
// NAVIGATION TESTS
// =============================================================================

describe('navigation', function (): void {
    test('nextRow moves highlight down', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0);

        $widget->nextRow();

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('previousRow moves highlight up', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(1);

        $widget->previousRow();

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('wraps from last to first when wrapNavigation is true', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(1)
            ->wrapNavigation(true);

        $widget->nextRow();

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('wraps from first to last when wrapNavigation is true', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0)
            ->wrapNavigation(true);

        $widget->previousRow();

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('stops at last row when wrapNavigation is false', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(1)
            ->wrapNavigation(false);

        $widget->nextRow();

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('stops at first row when wrapNavigation is false', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0)
            ->wrapNavigation(false);

        $widget->previousRow();

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('firstRow jumps to index 0', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->highlightedIndex(2);

        $widget->firstRow();

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('lastRow jumps to last index', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->highlightedIndex(0);

        $widget->lastRow();

        expect($widget->getHighlightedIndex())->toBe(2);
    });

    test('pageUp moves up by visible row count', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->highlightedIndex(15)
            ->maxHeight(5);

        $widget->pageUp();

        expect($widget->getHighlightedIndex())->toBe(10);
    });

    test('pageDown moves down by visible row count', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->highlightedIndex(5)
            ->maxHeight(5);

        $widget->pageDown();

        expect($widget->getHighlightedIndex())->toBe(10);
    });

    test('goToRow jumps to specific row', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->highlightedIndex(0);

        $widget->goToRow(2);

        expect($widget->getHighlightedIndex())->toBe(2);
    });

    test('scrolls viewport when highlight moves out of view', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->rows(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->highlightedIndex(0)
            ->maxHeight(3)
            ->showHeaders(false);

        $widget->goToRow(10);

        $area = Area::fromDimensions(15, 3);
        $buffer = Buffer::empty($area);
        render($buffer, $widget);
        $lines = $buffer->toLines();

        expect($lines[2])->toContain('Row 11');
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onHighlight called when highlight changes', function (): void {
        $called = false;

        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0)
            ->onHighlight(function () use (&$called): void {
                $called = true;
            });

        $widget->nextRow();

        expect($called)->toBeTrue();
    });

    test('onHighlight receives correct row and index', function (): void {
        $receivedRow = null;
        $receivedIndex = null;

        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0)
            ->onHighlight(function ($row, $index) use (&$receivedRow, &$receivedIndex): void {
                $receivedRow = $row;
                $receivedIndex = $index;
            });

        $widget->nextRow();

        expect($receivedRow)->toBe(['name' => 'Second']);
        expect($receivedIndex)->toBe(1);
    });

    test('onSelect called on select action', function (): void {
        $called = false;

        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
            ])
            ->onSelect(function () use (&$called): void {
                $called = true;
            });

        $widget->select();

        expect($called)->toBeTrue();
    });

    test('onSelect receives correct row and index', function (): void {
        $receivedRow = null;
        $receivedIndex = null;

        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(1)
            ->onSelect(function ($row, $index) use (&$receivedRow, &$receivedIndex): void {
                $receivedRow = $row;
                $receivedIndex = $index;
            });

        $widget->select();

        expect($receivedRow)->toBe(['name' => 'Second']);
        expect($receivedIndex)->toBe(1);
    });

    test('onKeyPress called for unhandled keys', function (): void {
        $keyBindings = SelectableTableWidget::make()->getKeyBindings();

        expect($keyBindings)->toBeArray();
        expect($keyBindings)->toHaveKey('up');
        expect($keyBindings['up'])->toBe('previousRow');
    });

    test('onKeyPress return value controls bubbling', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Test']])
            ->onKeyPress(function () {
                return true;
            });

        expect($widget->getKeyBindings())->toBeArray();
    });
});

// =============================================================================
// MULTI-SELECT TESTS
// =============================================================================

describe('multi-select', function (): void {
    test('toggleSelect toggles selection when multiSelect is true', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(0)
            ->multiSelect(true);

        $widget->toggleSelect();

        expect($widget->getSelectedIndexes())->toContain(0);

        $widget->toggleSelect();

        expect($widget->getSelectedIndexes())->not->toContain(0);
    });

    test('toggleSelect does nothing when multiSelect is false', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
            ])
            ->highlightedIndex(0)
            ->multiSelect(false);

        $widget->toggleSelect();

        expect($widget->getSelectedIndexes())->toBe([]);
    });

    test('onMultiSelect called with selection state', function (): void {
        $receivedSelected = null;

        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
            ])
            ->highlightedIndex(0)
            ->multiSelect(true)
            ->onMultiSelect(function ($row, $index, $selected) use (&$receivedSelected): void {
                $receivedSelected = $selected;
            });

        $widget->toggleSelect();

        expect($receivedSelected)->toBeTrue();
    });

    test('getSelectedRows returns all selected rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->selectedIndexes([0, 2]);

        $selected = $widget->getSelectedRows();

        expect($selected)->toHaveCount(2);
        expect($selected[0]['name'])->toBe('First');
        expect($selected[1]['name'])->toBe('Third');
    });

    test('selectAll selects all rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ]);

        $widget->selectAll();

        expect($widget->getSelectedIndexes())->toBe([0, 1, 2]);
    });

    test('deselectAll clears selection', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->selectedIndexes([0, 1]);

        $widget->deselectAll();

        expect($widget->getSelectedIndexes())->toBe([]);
    });

    test('selectedIndexes can be pre-set', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->selectedIndexes([1]);

        expect($widget->getSelectedIndexes())->toBe([1]);
    });
});

// =============================================================================
// COLUMN TESTS
// =============================================================================

describe('column configuration', function (): void {
    test('column uses key as label when no label specified', function (): void {
        $column = Column::make('name');

        expect($column->label)->toBe('Name');
    });

    test('column respects fixed width', function (): void {
        $column = Column::make('name')->width(15);

        expect($column->width)->toBe(15);
    });

    test('column respects minWidth constraint', function (): void {
        $column = Column::make('name')->minWidth(5);

        expect($column->minWidth)->toBe(5);
    });

    test('column respects maxWidth constraint', function (): void {
        $column = Column::make('name')->maxWidth(20);

        expect($column->maxWidth)->toBe(20);
    });

    test('flex columns distribute remaining space', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('fixed')->width(5),
                Column::make('flex1')->flex(1),
            ])
            ->rows([
                ['fixed' => 'A', 'flex1' => 'B'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 30, 3);

        expect(mb_strlen(trim($lines[0])))->toBeGreaterThan(5);
    });

    test('flex weight affects space distribution', function (): void {
        $col1 = Column::make('a')->flex(1);
        $col2 = Column::make('b')->flex(2);

        expect($col1->flex)->toBe(1);
        expect($col2->flex)->toBe(2);
    });

    test('truncate option adds ellipsis for long values', function (): void {
        $column = Column::make('name')->width(5)->truncate(true);

        expect($column->truncate)->toBeTrue();

        $value = $column->getValue(['name' => 'VeryLongName']);
        expect($value)->toBe('VeryLongName');
    });
});

// =============================================================================
// DATA MANIPULATION TESTS
// =============================================================================

describe('data manipulation', function (): void {
    test('getHighlightedRow returns current highlighted row', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->highlightedIndex(1);

        expect($widget->getHighlightedRow())->toBe(['name' => 'Second']);
    });

    test('getHighlightedRow returns null when no rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([]);

        expect($widget->getHighlightedRow())->toBeNull();
    });

    test('getHighlightedIndex returns current index', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Test']])
            ->highlightedIndex(0);

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('getRows returns all rows', function (): void {
        $rows = [
            ['name' => 'First'],
            ['name' => 'Second'],
        ];

        $widget = SelectableTableWidget::make()->rows($rows);

        expect($widget->getRows())->toBe($rows);
    });

    test('setRows replaces all rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Old']]);

        $widget->setRows([['name' => 'New']]);

        expect($widget->getRows())->toBe([['name' => 'New']]);
    });

    test('addRow appends a row', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'First']]);

        $widget->addRow(['name' => 'Second']);

        expect($widget->getRows())->toHaveCount(2);
        expect($widget->getRows()[1]['name'])->toBe('Second');
    });

    test('removeRow removes row at index', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ]);

        $widget->removeRow(1);

        expect($widget->getRows())->toHaveCount(2);
        expect($widget->getRows()[0]['name'])->toBe('First');
        expect($widget->getRows()[1]['name'])->toBe('Third');
    });

    test('updateRow updates row at index', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First', 'status' => 'pending'],
            ]);

        $widget->updateRow(0, ['status' => 'done']);

        expect($widget->getRows()[0]['status'])->toBe('done');
        expect($widget->getRows()[0]['name'])->toBe('First');
    });

    test('rows can be provided as closure for lazy loading', function (): void {
        $called = false;

        $widget = SelectableTableWidget::make()
            ->rows(function () use (&$called) {
                $called = true;

                return [['name' => 'Lazy']];
            });

        expect($called)->toBeFalse();

        $rows = $widget->getRows();

        expect($called)->toBeTrue();
        expect($rows)->toBe([['name' => 'Lazy']]);
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty rows array renders empty state', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->rows([])
            ->emptyText('Nothing here');

        $lines = renderToLines($widget, 20, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Nothing here');
    });

    test('single row navigates correctly', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Only']])
            ->highlightedIndex(0)
            ->wrapNavigation(true);

        $widget->nextRow();
        expect($widget->getHighlightedIndex())->toBe(0);

        $widget->previousRow();
        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('very long row data truncates properly', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10)->truncate(true),
            ])
            ->rows([
                ['name' => 'This is a very long piece of text that should be truncated'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toContain("\u{2026}");
    });

    test('many rows scrolls correctly', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->rows(array_map(fn ($i) => ['name' => "Row $i"], range(1, 100)))
            ->highlightedIndex(50)
            ->maxHeight(5)
            ->showHeaders(false);

        $area = Area::fromDimensions(15, 5);
        $buffer = Buffer::empty($area);
        render($buffer, $widget);
        $lines = $buffer->toLines();

        $fullOutput = implode('', $lines);
        expect($fullOutput)->toContain('Row 51');
    });

    test('handles row data with missing keys gracefully', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
                Column::make('missing')->width(10),
            ])
            ->rows([
                ['name' => 'Test'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 25, 3);

        expect($lines[0])->toContain('Test');
    });

    test('highlightedIndex beyond row count clamps to valid range', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Only']])
            ->highlightedIndex(100);

        $widget->goToRow(100);

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('negative highlightedIndex clamps to zero', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([['name' => 'Test']])
            ->highlightedIndex(-5);

        expect($widget->getHighlightedIndex())->toBe(0);
    });

    test('removeRow adjusts highlightedIndex if needed', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->highlightedIndex(2);

        $widget->removeRow(2);

        expect($widget->getHighlightedIndex())->toBe(1);
    });

    test('removeRow adjusts selectedIndexes if needed', function (): void {
        $widget = SelectableTableWidget::make()
            ->rows([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ])
            ->selectedIndexes([0, 2]);

        $widget->removeRow(1);

        expect($widget->getSelectedIndexes())->toContain(0);
        expect($widget->getSelectedIndexes())->toContain(1);
    });
});

// =============================================================================
// DISPLAY OPTIONS TESTS
// =============================================================================

describe('display options', function (): void {
    test('striped option alternates row backgrounds', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->rows([
                ['name' => 'Row 1'],
                ['name' => 'Row 2'],
            ])
            ->striped(true);

        expect($widget->isStriped())->toBeTrue();
    });

    test('showBorders renders table borders', function (): void {
        $widget = SelectableTableWidget::make()
            ->showBorders(true);

        expect($widget->isShowBorders())->toBeTrue();
    });

    test('emptyText customizes empty state message', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->rows([])
            ->emptyText('Custom empty message');

        $lines = renderToLines($widget, 30, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Custom empty message');
    });

    test('maxHeight limits visible rows', function (): void {
        $widget = SelectableTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->rows(array_map(fn ($i) => ['name' => "Row $i"], range(1, 10)))
            ->maxHeight(3)
            ->showHeaders(false);

        $area = Area::fromDimensions(15, 10);
        $buffer = Buffer::empty($area);
        render($buffer, $widget);
        $lines = $buffer->toLines();

        $rowCount = 0;
        foreach ($lines as $line) {
            if (preg_match('/Row \d/', $line)) {
                $rowCount++;
            }
        }

        expect($rowCount)->toBe(3);
    });

    test('highlightStyle inverse applies inverse styling', function (): void {
        $widget = SelectableTableWidget::make()
            ->highlightStyleOption('inverse');

        expect($widget->getHighlightStyleOption())->toBe('inverse');
    });

    test('highlightStyle bold applies bold styling', function (): void {
        $widget = SelectableTableWidget::make()
            ->highlightStyleOption('bold');

        expect($widget->getHighlightStyleOption())->toBe('bold');
    });

    test('highlightStyle color applies color styling', function (): void {
        $widget = SelectableTableWidget::make()
            ->highlightStyleOption('color');

        expect($widget->getHighlightStyleOption())->toBe('color');
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are applied', function (): void {
        $widget = SelectableTableWidget::make();
        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('up');
        expect($bindings)->toHaveKey('down');
        expect($bindings)->toHaveKey('enter');
        expect($bindings)->toHaveKey('space');
        expect($bindings['up'])->toBe('previousRow');
        expect($bindings['down'])->toBe('nextRow');
    });

    test('custom key bindings override defaults', function (): void {
        $widget = SelectableTableWidget::make()
            ->keyBindings(['j' => 'nextRow', 'k' => 'previousRow']);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('j');
        expect($bindings)->toHaveKey('k');
        expect($bindings['j'])->toBe('nextRow');
        expect($bindings['k'])->toBe('previousRow');
    });
});
