<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\DataTable\Column;
use Crumbls\Tui\Extension\Core\Widget\DataTableWidget;

/**
 * DataTable Component Tests
 *
 * A component for displaying and navigating large tabular datasets.
 * Optimized for viewing data (like SQL query results), not for selection-based navigation.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders correct columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('id')->label('ID')->width(6),
                Column::make('title')->label('Title')->width(15),
                Column::make('status')->label('Status')->width(10),
            ])
            ->data([
                ['id' => 1, 'title' => 'Hello World', 'status' => 'publish'],
            ])
            ->showHeaders(true);

        $lines = renderToLines($widget, 40, 5);

        expect($lines[0])->toContain('ID');
        expect($lines[0])->toContain('Title');
        expect($lines[0])->toContain('Status');
    });

    test('renders correct rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('id')->width(6),
                Column::make('name')->width(15),
            ])
            ->data([
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second'],
                ['id' => 3, 'name' => 'Third'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 30, 5);

        expect($lines[0])->toContain('First');
        expect($lines[1])->toContain('Second');
        expect($lines[2])->toContain('Third');
    });

    test('truncates long values', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('title')->width(10),
            ])
            ->data([
                ['title' => 'This is a very long title that should be truncated'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 15, 3);

        expect($lines[0])->toContain("\u{2026}");
    });

    test('shows row numbers when enabled', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->showRowNumbers(true)
            ->showHeaders(false);

        $lines = renderToLines($widget, 20, 5);

        expect($lines[0])->toMatch('/1\s/');
        expect($lines[1])->toMatch('/2\s/');
    });

    test('shows count indicator', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 50)))
            ->showCount(true)
            ->maxHeight(10)
            ->title('Test Table');

        $lines = renderToLines($widget, 50, 15);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toMatch('/\d+-\d+ of \d+/');
    });

    test('shows scroll indicators when needed', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1')->width(15),
                Column::make('col2')->width(15),
                Column::make('col3')->width(15),
                Column::make('col4')->width(15),
            ])
            ->data([
                ['col1' => 'A', 'col2' => 'B', 'col3' => 'C', 'col4' => 'D'],
            ])
            ->showScrollIndicators(true)
            ->maxWidth(30)
            ->showHeaders(false);

        $lines = renderToLines($widget, 35, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain("\u{2192}");
    });

    test('hides scroll indicators when all content visible', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data([
                ['name' => 'Short'],
            ])
            ->showScrollIndicators(true)
            ->showHeaders(false);

        $lines = renderToLines($widget, 20, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->not->toContain("\u{2192}");
        expect($fullOutput)->not->toContain("\u{2190}");
    });

    test('applies striping when enabled', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data([
                ['name' => 'Row 1'],
                ['name' => 'Row 2'],
                ['name' => 'Row 3'],
            ])
            ->striped(true);

        expect($widget->isStriped())->toBeTrue();
    });
});

// =============================================================================
// SCROLLING TESTS
// =============================================================================

describe('scrolling', function (): void {
    test('vertical scroll changes visible rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->maxHeight(5)
            ->showHeaders(false);

        $widget->scrollDown(5);

        $lines = renderToLines($widget, 15, 5);

        expect($lines[0])->toContain('Row 6');
    });

    test('horizontal scroll changes visible columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1')->label('Column1')->width(10),
                Column::make('col2')->label('Column2')->width(10),
                Column::make('col3')->label('Column3')->width(10),
            ])
            ->data([
                ['col1' => 'A', 'col2' => 'B', 'col3' => 'C'],
            ])
            ->maxWidth(15)
            ->showHeaders(true)
            ->showScrollIndicators(false);

        $widget->scrollRight(1);

        $lines = renderToLines($widget, 25, 5);

        expect($lines[0])->toContain('Column2');
        expect($lines[0])->not->toContain('Column1');
    });

    test('page up/down moves by page size', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 50)))
            ->pageSize(10)
            ->currentRow(25);

        $widget->pageUp();

        expect($widget->getCurrentRow())->toBe(15);

        $widget->pageDown();

        expect($widget->getCurrentRow())->toBe(25);
    });

    test('home/end jump to boundaries', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 50)))
            ->currentRow(25);

        $widget->firstRow();
        expect($widget->getCurrentRow())->toBe(0);

        $widget->lastRow();
        expect($widget->getCurrentRow())->toBe(49);
    });

    test('go to row navigates correctly', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 50)))
            ->currentRow(0);

        $widget->goToRow(30);

        expect($widget->getCurrentRow())->toBe(30);
    });

    test('scroll indicators update on scroll', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1')->label('Col1')->width(10),
                Column::make('col2')->label('Col2')->width(10),
                Column::make('col3')->label('Col3')->width(10),
            ])
            ->data([
                ['col1' => 'A', 'col2' => 'B', 'col3' => 'C'],
            ])
            ->showScrollIndicators(true)
            ->maxWidth(15)
            ->showHeaders(false);

        $widget->scrollRight(1);

        $lines = renderToLines($widget, 20, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain("\u{2190}");
    });
});

// =============================================================================
// SEARCH TESTS
// =============================================================================

describe('search', function (): void {
    test('search filters visible rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Banana'],
                ['name' => 'Apricot'],
                ['name' => 'Cherry'],
            ])
            ->searchable(true)
            ->showHeaders(false);

        $widget->search('Ap');

        $visibleRows = $widget->getVisibleRows();

        expect($visibleRows)->toHaveCount(2);
        expect($visibleRows[0]['name'])->toBe('Apple');
        expect($visibleRows[1]['name'])->toBe('Apricot');
    });

    test('search highlights matches', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Banana'],
            ])
            ->searchable(true);

        $widget->search('Apple');

        $matchCount = $widget->getMatchCount();

        expect($matchCount)->toBeGreaterThan(0);
    });

    test('next/previous match navigation works', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Banana'],
                ['name' => 'Apricot'],
            ])
            ->searchable(true);

        $widget->search('A');

        $initialRow = $widget->getCurrentRow();
        $widget->nextMatch();
        $nextRow = $widget->getCurrentRow();

        expect($nextRow)->not->toBe($initialRow);

        $widget->previousMatch();

        expect($widget->getCurrentRow())->toBe($initialRow);
    });

    test('clear search restores full data', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Banana'],
                ['name' => 'Cherry'],
            ])
            ->searchable(true);

        $widget->search('Apple');
        expect($widget->getVisibleRows())->toHaveCount(1);

        $widget->clearSearch();
        expect($widget->getVisibleRows())->toHaveCount(3);
    });

    test('empty search shows all rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Banana'],
            ])
            ->searchable(true);

        $widget->search('');

        expect($widget->getVisibleRows())->toHaveCount(2);
    });

    test('search across multiple columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
                Column::make('status')->width(10),
            ])
            ->data([
                ['name' => 'Apple', 'status' => 'ripe'],
                ['name' => 'Banana', 'status' => 'unripe'],
                ['name' => 'Cherry', 'status' => 'ripe'],
            ])
            ->searchable(true)
            ->searchColumns(['name', 'status']);

        $widget->search('ripe');

        $visibleRows = $widget->getVisibleRows();

        expect($visibleRows)->toHaveCount(3);
    });
});

// =============================================================================
// SORTING TESTS
// =============================================================================

describe('sorting', function (): void {
    test('sort ascending by column', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15)->sortable(true),
            ])
            ->data([
                ['name' => 'Cherry'],
                ['name' => 'Apple'],
                ['name' => 'Banana'],
            ]);

        $widget->sortBy('name', 'asc');

        $data = $widget->getData();

        expect($data[0]['name'])->toBe('Apple');
        expect($data[1]['name'])->toBe('Banana');
        expect($data[2]['name'])->toBe('Cherry');
    });

    test('sort descending by column', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15)->sortable(true),
            ])
            ->data([
                ['name' => 'Apple'],
                ['name' => 'Cherry'],
                ['name' => 'Banana'],
            ]);

        $widget->sortBy('name', 'desc');

        $data = $widget->getData();

        expect($data[0]['name'])->toBe('Cherry');
        expect($data[1]['name'])->toBe('Banana');
        expect($data[2]['name'])->toBe('Apple');
    });

    test('toggle sort reverses direction', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15)->sortable(true),
            ])
            ->data([
                ['name' => 'Cherry'],
                ['name' => 'Apple'],
                ['name' => 'Banana'],
            ]);

        $widget->sortBy('name', 'asc');
        expect($widget->getData()[0]['name'])->toBe('Apple');

        $widget->toggleSort('name');
        expect($widget->getData()[0]['name'])->toBe('Cherry');
    });

    test('clear sort restores original order', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15)->sortable(true),
            ])
            ->data([
                ['name' => 'Cherry'],
                ['name' => 'Apple'],
                ['name' => 'Banana'],
            ]);

        $widget->sortBy('name', 'asc');
        $widget->clearSort();

        $data = $widget->getData();

        expect($data[0]['name'])->toBe('Cherry');
        expect($data[1]['name'])->toBe('Apple');
        expect($data[2]['name'])->toBe('Banana');
    });

    test('sort indicator shown in header', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->label('Name')->width(15)->sortable(true),
            ])
            ->data([
                ['name' => 'Apple'],
            ])
            ->showHeaders(true);

        $widget->sortBy('name', 'asc');

        $lines = renderToLines($widget, 25, 5);

        expect($lines[0])->toMatch('/Name.*[\x{25B2}\x{25BC}\x{2191}\x{2193}]/u');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty data shows empty state', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([]);

        $lines = renderToLines($widget, 30, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toMatch('/no data|empty|no rows/i');
    });

    test('single row/column renders correctly', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
            ])
            ->data([
                ['name' => 'Only Row'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 20, 5);

        expect($lines[0])->toContain('Only Row');
    });

    test('very wide data scrolls horizontally', function (): void {
        $widget = DataTableWidget::make()
            ->columns(array_map(
                fn ($i) => Column::make("col$i")->width(10),
                range(1, 10)
            ))
            ->data([
                array_combine(
                    array_map(fn ($i) => "col$i", range(1, 10)),
                    range(1, 10)
                ),
            ])
            ->maxWidth(25)
            ->showHeaders(false);

        expect($widget->getTotalColumns())->toBe(10);

        $visibleCols = $widget->getVisibleColumns();
        expect(count($visibleCols))->toBeLessThan(10);
    });

    test('very tall data scrolls vertically', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 100)))
            ->maxHeight(10)
            ->showHeaders(false);

        expect($widget->getTotalRows())->toBe(100);

        $visibleRows = $widget->getVisibleRows();
        expect(count($visibleRows))->toBeLessThanOrEqual(10);
    });

    test('null/undefined values handled gracefully', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(15),
                Column::make('value')->width(10),
            ])
            ->data([
                ['name' => 'Test', 'value' => null],
                ['name' => null, 'value' => 'Data'],
                ['name' => 'Missing'],
            ])
            ->showHeaders(false);

        $lines = renderToLines($widget, 30, 5);

        expect($lines[0])->toContain('Test');
        expect($lines[2])->toContain('Missing');
    });
});

// =============================================================================
// DATA ACCESS TESTS
// =============================================================================

describe('data access', function (): void {
    test('getData returns all data', function (): void {
        $data = [
            ['name' => 'First'],
            ['name' => 'Second'],
        ];

        $widget = DataTableWidget::make()
            ->data($data);

        expect($widget->getData())->toBe($data);
    });

    test('getVisibleRows returns current viewport rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('name')->width(10),
            ])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->maxHeight(5)
            ->showHeaders(false);

        $visibleRows = $widget->getVisibleRows();

        expect($visibleRows)->toHaveCount(5);
        expect($visibleRows[0]['name'])->toBe('Row 1');
    });

    test('getVisibleColumns returns current viewport columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1')->width(10),
                Column::make('col2')->width(10),
                Column::make('col3')->width(10),
            ])
            ->data([
                ['col1' => 'A', 'col2' => 'B', 'col3' => 'C'],
            ])
            ->maxWidth(15);

        $visibleCols = $widget->getVisibleColumns();

        expect(count($visibleCols))->toBeLessThan(3);
    });

    test('getTotalRows returns count of all rows', function (): void {
        $widget = DataTableWidget::make()
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 50)));

        expect($widget->getTotalRows())->toBe(50);
    });

    test('getTotalColumns returns count of all columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1'),
                Column::make('col2'),
                Column::make('col3'),
            ]);

        expect($widget->getTotalColumns())->toBe(3);
    });

    test('getRow returns specific row by index', function (): void {
        $widget = DataTableWidget::make()
            ->data([
                ['name' => 'First'],
                ['name' => 'Second'],
                ['name' => 'Third'],
            ]);

        expect($widget->getRow(1))->toBe(['name' => 'Second']);
        expect($widget->getRow(100))->toBeNull();
    });
});

// =============================================================================
// COLUMN CONFIGURATION TESTS
// =============================================================================

describe('column configuration', function (): void {
    test('column uses key as label when no label specified', function (): void {
        $column = Column::make('userName');

        expect($column->label)->toBe('UserName');
    });

    test('column respects custom label', function (): void {
        $column = Column::make('id')->label('ID');

        expect($column->label)->toBe('ID');
    });

    test('column width is configurable', function (): void {
        $column = Column::make('name')->width(20);

        expect($column->width)->toBe(20);
    });

    test('column minWidth/maxWidth constraints work', function (): void {
        $column = Column::make('name')->minWidth(5)->maxWidth(50);

        expect($column->minWidth)->toBe(5);
        expect($column->maxWidth)->toBe(50);
    });

    test('column alignment options work', function (): void {
        $left = Column::make('a')->align('left');
        $center = Column::make('b')->align('center');
        $right = Column::make('c')->align('right');

        expect($left->align)->toBe('left');
        expect($center->align)->toBe('center');
        expect($right->align)->toBe('right');
    });

    test('column format function transforms values', function (): void {
        $column = Column::make('price')
            ->format(fn ($value) => '$' . number_format((float) $value, 2));

        $formatted = $column->formatValue(1234.5, []);

        expect($formatted)->toBe('$1,234.50');
    });

    test('column sortable flag works', function (): void {
        $sortable = Column::make('name')->sortable(true);
        $notSortable = Column::make('id')->sortable(false);

        expect($sortable->sortable)->toBeTrue();
        expect($notSortable->sortable)->toBeFalse();
    });

    test('column visibility can be toggled', function (): void {
        $visible = Column::make('name')->visible(true);
        $hidden = Column::make('secret')->visible(false);

        expect($visible->visible)->toBeTrue();
        expect($hidden->visible)->toBeFalse();
    });

    test('column wrap option works', function (): void {
        $wrap = Column::make('description')->wrap(true);
        $noWrap = Column::make('id')->wrap(false);

        expect($wrap->wrap)->toBeTrue();
        expect($noWrap->wrap)->toBeFalse();
    });
});

// =============================================================================
// DISPLAY OPTIONS TESTS
// =============================================================================

describe('display options', function (): void {
    test('showHeaders controls header visibility', function (): void {
        $withHeaders = DataTableWidget::make()
            ->columns([Column::make('name')->label('Name')->width(10)])
            ->data([['name' => 'Test']])
            ->showHeaders(true);

        $lines = renderToLines($withHeaders, 20, 5);
        expect($lines[0])->toContain('Name');

        $withoutHeaders = DataTableWidget::make()
            ->columns([Column::make('name')->label('Name')->width(10)])
            ->data([['name' => 'Test']])
            ->showHeaders(false);

        $lines = renderToLines($withoutHeaders, 20, 5);
        expect($lines[0])->not->toContain('Name');
        expect($lines[0])->toContain('Test');
    });

    test('showBorders controls border rendering', function (): void {
        $widget = DataTableWidget::make()
            ->showBorders(true);

        expect($widget->isShowBorders())->toBeTrue();
    });

    test('title is displayed when set', function (): void {
        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->data([['name' => 'Test']])
            ->title('My Table');

        $lines = renderToLines($widget, 30, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('My Table');
    });

    test('maxHeight limits visible rows', function (): void {
        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->maxHeight(5)
            ->showHeaders(false);

        $visibleRows = $widget->getVisibleRows();

        expect(count($visibleRows))->toBe(5);
    });

    test('maxWidth limits visible columns', function (): void {
        $widget = DataTableWidget::make()
            ->columns([
                Column::make('col1')->width(10),
                Column::make('col2')->width(10),
                Column::make('col3')->width(10),
            ])
            ->data([['col1' => 'A', 'col2' => 'B', 'col3' => 'C']])
            ->maxWidth(15);

        $visibleCols = $widget->getVisibleColumns();

        expect(count($visibleCols))->toBeLessThan(3);
    });
});

// =============================================================================
// AUTO COLUMNS TESTS
// =============================================================================

describe('auto columns', function (): void {
    test('autoColumns detects columns from first row', function (): void {
        $widget = DataTableWidget::make()
            ->data([
                ['id' => 1, 'name' => 'Test', 'status' => 'active'],
            ])
            ->autoColumns(true);

        expect($widget->getTotalColumns())->toBe(3);
    });

    test('autoColumns works with empty data', function (): void {
        $widget = DataTableWidget::make()
            ->data([])
            ->autoColumns(true);

        expect($widget->getTotalColumns())->toBe(0);
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onScroll callback is triggered', function (): void {
        $scrollRow = null;
        $scrollCol = null;

        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->data(array_map(fn ($i) => ['name' => "Row $i"], range(1, 20)))
            ->onScroll(function ($row, $col) use (&$scrollRow, &$scrollCol): void {
                $scrollRow = $row;
                $scrollCol = $col;
            });

        $widget->scrollDown(5);

        expect($scrollRow)->toBe(5);
    });

    test('onSearch callback is triggered', function (): void {
        $searchQuery = null;

        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->data([['name' => 'Test']])
            ->searchable(true)
            ->onSearch(function ($query) use (&$searchQuery): void {
                $searchQuery = $query;
            });

        $widget->search('test');

        expect($searchQuery)->toBe('test');
    });

    test('onRowFocus callback is triggered', function (): void {
        $focusedRow = null;
        $focusedIndex = null;

        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)])
            ->data([
                ['name' => 'First'],
                ['name' => 'Second'],
            ])
            ->onRowFocus(function ($row, $index) use (&$focusedRow, &$focusedIndex): void {
                $focusedRow = $row;
                $focusedIndex = $index;
            });

        $widget->goToRow(1);

        expect($focusedIndex)->toBe(1);
        expect($focusedRow)->toBe(['name' => 'Second']);
    });

    test('onColumnSort callback is triggered', function (): void {
        $sortedColumn = null;
        $sortedDirection = null;

        $widget = DataTableWidget::make()
            ->columns([Column::make('name')->width(10)->sortable(true)])
            ->data([['name' => 'Test']])
            ->onColumnSort(function ($column, $direction) use (&$sortedColumn, &$sortedDirection): void {
                $sortedColumn = $column;
                $sortedDirection = $direction;
            });

        $widget->sortBy('name', 'desc');

        expect($sortedColumn)->toBe('name');
        expect($sortedDirection)->toBe('desc');
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are applied', function (): void {
        $widget = DataTableWidget::make();
        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('up');
        expect($bindings)->toHaveKey('down');
        expect($bindings)->toHaveKey('left');
        expect($bindings)->toHaveKey('right');
        expect($bindings['up'])->toBe('scrollUp');
        expect($bindings['down'])->toBe('scrollDown');
    });

    test('custom key bindings override defaults', function (): void {
        $widget = DataTableWidget::make()
            ->keyBindings([
                'j' => 'scrollDown',
                'k' => 'scrollUp',
            ]);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('j');
        expect($bindings)->toHaveKey('k');
        expect($bindings['j'])->toBe('scrollDown');
    });
});
