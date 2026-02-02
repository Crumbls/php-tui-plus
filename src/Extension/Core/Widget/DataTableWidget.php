<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Extension\Core\Widget\DataTable\Column;
use Crumbls\Tui\Widget\Widget;

final class DataTableWidget implements Widget
{
    /** @var list<Column> */
    private array $columns = [];

    /** @var array<int, array<string, mixed>> */
    private array $data = [];

    /** @var array<int, array<string, mixed>> */
    private array $originalData = [];

    private int $scrollRow = 0;

    private int $scrollCol = 0;

    private int $currentRow = 0;

    private int $pageSize = 20;

    private bool $showHeaders = true;

    private bool $showRowNumbers = false;

    private bool $showBorders = true;

    private bool $showScrollIndicators = true;

    private bool $striped = false;

    private ?int $maxHeight = null;

    private ?int $maxWidth = null;

    private ?string $title = null;

    private bool $showCount = false;

    private bool $searchable = false;

    private ?string $searchQuery = null;

    /** @var array<int, int> */
    private array $searchMatches = [];

    private int $currentMatch = 0;

    /** @var array<int, string> */
    private array $searchColumns = [];

    private ?Closure $filterCallback = null;

    private ?string $sortColumn = null;

    private string $sortDirection = 'asc';

    private bool $autoColumnsEnabled = false;

    private ?Closure $onScroll = null;

    private ?Closure $onSearch = null;

    private ?Closure $onRowFocus = null;

    private ?Closure $onColumnSort = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'up' => 'scrollUp',
        'down' => 'scrollDown',
        'left' => 'scrollLeft',
        'right' => 'scrollRight',
        'pageUp' => 'pageUp',
        'pageDown' => 'pageDown',
        'home' => 'firstRow',
        'end' => 'lastRow',
        'g' => 'goToRow',
        '/' => 'search',
        'n' => 'nextMatch',
        'N' => 'previousMatch',
        'escape' => 'clearSearch',
    ];

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param list<Column> $columns
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     */
    public function data(array $data): self
    {
        $this->data = $data;
        $this->originalData = $data;

        if ($this->autoColumnsEnabled && count($this->columns) === 0 && count($data) > 0) {
            $this->generateColumnsFromData();
        }

        return $this;
    }

    public function autoColumns(bool $auto = true): self
    {
        $this->autoColumnsEnabled = $auto;

        if ($auto && count($this->columns) === 0 && count($this->data) > 0) {
            $this->generateColumnsFromData();
        }

        return $this;
    }

    public function showHeaders(bool $show = true): self
    {
        $this->showHeaders = $show;

        return $this;
    }

    public function showRowNumbers(bool $show = false): self
    {
        $this->showRowNumbers = $show;

        return $this;
    }

    public function showBorders(bool $show = true): self
    {
        $this->showBorders = $show;

        return $this;
    }

    public function showScrollIndicators(bool $show = true): self
    {
        $this->showScrollIndicators = $show;

        return $this;
    }

    public function striped(bool $striped = true): self
    {
        $this->striped = $striped;

        return $this;
    }

    public function maxHeight(int $rows): self
    {
        $this->maxHeight = $rows;

        return $this;
    }

    public function maxWidth(int $chars): self
    {
        $this->maxWidth = $chars;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function showCount(bool $show = true): self
    {
        $this->showCount = $show;

        return $this;
    }

    public function currentRow(int $row = 0): self
    {
        $this->currentRow = max(0, min($row, $this->getTotalRows() - 1));

        return $this;
    }

    public function currentColumn(int $col = 0): self
    {
        $this->scrollCol = max(0, $col);

        return $this;
    }

    public function pageSize(int $rows = 20): self
    {
        $this->pageSize = $rows;

        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * @param array<int, string> $columns
     */
    public function searchColumns(array $columns = []): self
    {
        $this->searchColumns = $columns;

        return $this;
    }

    public function filterCallback(Closure $fn): self
    {
        $this->filterCallback = $fn;

        return $this;
    }

    public function onScroll(Closure $fn): self
    {
        $this->onScroll = $fn;

        return $this;
    }

    public function onSearch(Closure $fn): self
    {
        $this->onSearch = $fn;

        return $this;
    }

    public function onRowFocus(Closure $fn): self
    {
        $this->onRowFocus = $fn;

        return $this;
    }

    public function onColumnSort(Closure $fn): self
    {
        $this->onColumnSort = $fn;

        return $this;
    }

    /**
     * @param array<string, string> $bindings
     */
    public function keyBindings(array $bindings): self
    {
        $this->keyBindings = array_merge($this->keyBindings, $bindings);

        return $this;
    }

    public function scrollUp(int $rows = 1): self
    {
        $this->scrollRow = max(0, $this->scrollRow - $rows);
        $this->triggerOnScroll();

        return $this;
    }

    public function scrollDown(int $rows = 1): self
    {
        $totalRows = $this->getTotalRows();
        $visibleRows = $this->maxHeight ?? 1;
        $maxScroll = max(0, $totalRows - $visibleRows);
        $newScrollRow = $this->scrollRow + $rows;
        $this->scrollRow = min($maxScroll, max(0, $newScrollRow));

        if ($this->maxHeight === null && $totalRows > 0) {
            $this->scrollRow = min($totalRows - 1, $newScrollRow);
        }

        $this->triggerOnScroll();

        return $this;
    }

    public function scrollLeft(int $cols = 1): self
    {
        $this->scrollCol = max(0, $this->scrollCol - $cols);
        $this->triggerOnScroll();

        return $this;
    }

    public function scrollRight(int $cols = 1): self
    {
        $maxScroll = max(0, $this->getTotalColumns() - 1);
        $this->scrollCol = min($maxScroll, $this->scrollCol + $cols);
        $this->triggerOnScroll();

        return $this;
    }

    public function pageUp(): self
    {
        $this->currentRow = max(0, $this->currentRow - $this->pageSize);
        $this->triggerOnRowFocus();

        return $this;
    }

    public function pageDown(): self
    {
        $this->currentRow = min($this->getTotalRows() - 1, $this->currentRow + $this->pageSize);
        $this->currentRow = max(0, $this->currentRow);
        $this->triggerOnRowFocus();

        return $this;
    }

    public function firstRow(): self
    {
        $this->currentRow = 0;
        $this->triggerOnRowFocus();

        return $this;
    }

    public function lastRow(): self
    {
        $this->currentRow = max(0, $this->getTotalRows() - 1);
        $this->triggerOnRowFocus();

        return $this;
    }

    public function goToRow(int $row): self
    {
        $this->currentRow = max(0, min($row, $this->getTotalRows() - 1));
        $this->triggerOnRowFocus();

        return $this;
    }

    public function goToColumn(int $col): self
    {
        $this->scrollCol = max(0, min($col, $this->getTotalColumns() - 1));
        $this->triggerOnScroll();

        return $this;
    }

    public function search(string $query): self
    {
        $this->searchQuery = $query;
        $this->currentMatch = 0;
        $this->searchMatches = [];

        if ($this->onSearch !== null) {
            ($this->onSearch)($query);
        }

        if ($query === '') {
            return $this;
        }

        $filteredData = [];
        $matchIndex = 0;

        foreach ($this->originalData as $index => $row) {
            $matches = false;

            if ($this->filterCallback !== null) {
                $matches = ($this->filterCallback)($row, $query);
            } else {
                $columnsToSearch = empty($this->searchColumns)
                    ? array_keys($row)
                    : $this->searchColumns;

                foreach ($columnsToSearch as $col) {
                    $value = $row[$col] ?? '';
                    if (stripos((string) $value, $query) !== false) {
                        $matches = true;
                        break;
                    }
                }
            }

            if ($matches) {
                $filteredData[] = $row;
                $this->searchMatches[$matchIndex] = $index;
                $matchIndex++;
            }
        }

        $this->data = $filteredData;

        return $this;
    }

    public function clearSearch(): self
    {
        $this->searchQuery = null;
        $this->searchMatches = [];
        $this->currentMatch = 0;
        $this->data = $this->originalData;

        return $this;
    }

    public function nextMatch(): self
    {
        if (count($this->searchMatches) === 0) {
            return $this;
        }

        $this->currentMatch = ($this->currentMatch + 1) % count($this->searchMatches);
        $this->currentRow = $this->currentMatch;
        $this->triggerOnRowFocus();

        return $this;
    }

    public function previousMatch(): self
    {
        if (count($this->searchMatches) === 0) {
            return $this;
        }

        $this->currentMatch = ($this->currentMatch - 1 + count($this->searchMatches)) % count($this->searchMatches);
        $this->currentRow = $this->currentMatch;
        $this->triggerOnRowFocus();

        return $this;
    }

    public function getMatchCount(): int
    {
        return count($this->searchMatches);
    }

    public function sortBy(string $column, string $direction = 'asc'): self
    {
        $this->sortColumn = $column;
        $this->sortDirection = $direction;

        usort($this->data, function (array $a, array $b) use ($column, $direction): int {
            $valA = $a[$column] ?? '';
            $valB = $b[$column] ?? '';

            $result = $valA <=> $valB;

            return $direction === 'desc' ? -$result : $result;
        });

        if ($this->onColumnSort !== null) {
            ($this->onColumnSort)($column, $direction);
        }

        return $this;
    }

    public function toggleSort(string $column): self
    {
        if ($this->sortColumn === $column && $this->sortDirection === 'asc') {
            return $this->sortBy($column, 'desc');
        }

        return $this->sortBy($column, 'asc');
    }

    public function clearSort(): self
    {
        $this->sortColumn = null;
        $this->sortDirection = 'asc';
        $this->data = $this->originalData;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVisibleRows(): array
    {
        $maxRows = $this->maxHeight ?? count($this->data);
        $start = $this->scrollRow;

        return array_slice($this->data, $start, $maxRows);
    }

    /**
     * @return list<Column>
     */
    public function getVisibleColumns(): array
    {
        if ($this->maxWidth === null) {
            return array_slice($this->columns, $this->scrollCol);
        }

        $visibleColumns = [];
        $totalWidth = 0;

        for ($i = $this->scrollCol; $i < count($this->columns); $i++) {
            $column = $this->columns[$i];
            $columnWidth = $column->width ?? 10;

            if ($totalWidth + $columnWidth > $this->maxWidth) {
                break;
            }

            $visibleColumns[] = $column;
            $totalWidth += $columnWidth;
        }

        return $visibleColumns;
    }

    public function getTotalRows(): int
    {
        return count($this->data);
    }

    public function getTotalColumns(): int
    {
        return count($this->columns);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRow(int $index): ?array
    {
        return $this->data[$index] ?? null;
    }

    public function getCurrentRow(): int
    {
        return $this->currentRow;
    }

    public function getScrollRow(): int
    {
        return $this->scrollRow;
    }

    public function getScrollCol(): int
    {
        return $this->scrollCol;
    }

    /**
     * @return list<Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function isShowHeaders(): bool
    {
        return $this->showHeaders;
    }

    public function isShowRowNumbers(): bool
    {
        return $this->showRowNumbers;
    }

    public function isShowBorders(): bool
    {
        return $this->showBorders;
    }

    public function isShowScrollIndicators(): bool
    {
        return $this->showScrollIndicators;
    }

    public function isStriped(): bool
    {
        return $this->striped;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isShowCount(): bool
    {
        return $this->showCount;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    private function generateColumnsFromData(): void
    {
        if (count($this->data) === 0) {
            return;
        }

        $firstRow = $this->data[0];
        $this->columns = [];

        foreach (array_keys($firstRow) as $key) {
            $this->columns[] = Column::make($key);
        }
    }

    private function triggerOnScroll(): void
    {
        if ($this->onScroll !== null) {
            ($this->onScroll)($this->scrollRow, $this->scrollCol);
        }
    }

    private function triggerOnRowFocus(): void
    {
        if ($this->onRowFocus !== null) {
            $row = $this->getRow($this->currentRow);
            if ($row !== null) {
                ($this->onRowFocus)($row, $this->currentRow);
            }
        }
    }
}
