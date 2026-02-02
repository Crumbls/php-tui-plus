<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Extension\Core\Widget\SelectableTable\Column;
use Crumbls\Tui\Widget\Widget;

final class SelectableTableWidget implements Widget
{
    /** @var list<Column> */
    private array $columns = [];

    /** @var array<int, array<string, mixed>>|Closure */
    private array|Closure $rows = [];

    /** @var array<int, array<string, mixed>>|null */
    private ?array $resolvedRows = null;

    private int $highlightedIndex = 0;

    /** @var list<int> */
    private array $selectedIndexes = [];

    private int $scrollOffset = 0;

    private bool $multiSelectEnabled = false;

    private bool $showHeaders = true;

    private bool $showBorders = true;

    private bool $striped = false;

    private string $highlightStyle = 'inverse';

    private string $emptyText = 'No items';

    private ?int $maxHeight = null;

    private bool $wrapNavigation = true;

    private ?Closure $onHighlight = null;

    private ?Closure $onSelect = null;

    private ?Closure $onMultiSelect = null;

    private ?Closure $onKeyPress = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'up' => 'previousRow',
        'down' => 'nextRow',
        'enter' => 'select',
        'space' => 'toggleSelect',
        'home' => 'firstRow',
        'end' => 'lastRow',
        'pageUp' => 'pageUp',
        'pageDown' => 'pageDown',
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
     * @param array<int, array<string, mixed>>|Closure $rows
     */
    public function rows(array|Closure $rows): self
    {
        $this->rows = $rows;
        $this->resolvedRows = null;

        return $this;
    }

    public function showHeaders(bool $show = true): self
    {
        $this->showHeaders = $show;

        return $this;
    }

    public function showBorders(bool $show = true): self
    {
        $this->showBorders = $show;

        return $this;
    }

    public function striped(bool $striped = false): self
    {
        $this->striped = $striped;

        return $this;
    }

    public function highlightStyleOption(string $style): self
    {
        $this->highlightStyle = $style;

        return $this;
    }

    public function emptyText(string $text): self
    {
        $this->emptyText = $text;

        return $this;
    }

    public function maxHeight(int $rows): self
    {
        $this->maxHeight = $rows;

        return $this;
    }

    public function highlightedIndex(int $index): self
    {
        $this->highlightedIndex = max(0, $index);

        return $this;
    }

    /**
     * @param list<int> $indexes
     */
    public function selectedIndexes(array $indexes): self
    {
        $this->selectedIndexes = $indexes;

        return $this;
    }

    public function multiSelect(bool $enabled = false): self
    {
        $this->multiSelectEnabled = $enabled;

        return $this;
    }

    public function wrapNavigation(bool $wrap = true): self
    {
        $this->wrapNavigation = $wrap;

        return $this;
    }

    public function onHighlight(Closure $fn): self
    {
        $this->onHighlight = $fn;

        return $this;
    }

    public function onSelect(Closure $fn): self
    {
        $this->onSelect = $fn;

        return $this;
    }

    public function onMultiSelect(Closure $fn): self
    {
        $this->onMultiSelect = $fn;

        return $this;
    }

    public function onKeyPress(Closure $fn): self
    {
        $this->onKeyPress = $fn;

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

    public function previousRow(): self
    {
        $rows = $this->getRows();
        $count = count($rows);

        if ($count === 0) {
            return $this;
        }

        $oldIndex = $this->highlightedIndex;

        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        } elseif ($this->wrapNavigation) {
            $this->highlightedIndex = $count - 1;
        }

        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function nextRow(): self
    {
        $rows = $this->getRows();
        $count = count($rows);

        if ($count === 0) {
            return $this;
        }

        $oldIndex = $this->highlightedIndex;

        if ($this->highlightedIndex < $count - 1) {
            $this->highlightedIndex++;
        } elseif ($this->wrapNavigation) {
            $this->highlightedIndex = 0;
        }

        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function firstRow(): self
    {
        $oldIndex = $this->highlightedIndex;
        $this->highlightedIndex = 0;
        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function lastRow(): self
    {
        $rows = $this->getRows();
        $oldIndex = $this->highlightedIndex;
        $this->highlightedIndex = max(0, count($rows) - 1);
        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function pageUp(): self
    {
        $pageSize = $this->maxHeight ?? 10;
        $oldIndex = $this->highlightedIndex;
        $this->highlightedIndex = max(0, $this->highlightedIndex - $pageSize);
        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function pageDown(): self
    {
        $rows = $this->getRows();
        $pageSize = $this->maxHeight ?? 10;
        $oldIndex = $this->highlightedIndex;
        $this->highlightedIndex = min(count($rows) - 1, $this->highlightedIndex + $pageSize);
        $this->highlightedIndex = max(0, $this->highlightedIndex);
        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function goToRow(int $index): self
    {
        $rows = $this->getRows();
        $oldIndex = $this->highlightedIndex;
        $this->highlightedIndex = max(0, min($index, count($rows) - 1));
        $this->triggerOnHighlight($oldIndex);

        return $this;
    }

    public function select(): void
    {
        if ($this->onSelect === null) {
            return;
        }

        $row = $this->getHighlightedRow();

        if ($row !== null) {
            ($this->onSelect)($row, $this->highlightedIndex);
        }
    }

    public function toggleSelect(): void
    {
        if (!$this->multiSelectEnabled) {
            return;
        }

        $index = $this->highlightedIndex;
        $key = array_search($index, $this->selectedIndexes, true);

        if ($key !== false) {
            unset($this->selectedIndexes[$key]);
            $this->selectedIndexes = array_values($this->selectedIndexes);
            $selected = false;
        } else {
            $this->selectedIndexes[] = $index;
            $selected = true;
        }

        if ($this->onMultiSelect !== null) {
            $row = $this->getHighlightedRow();
            if ($row !== null) {
                ($this->onMultiSelect)($row, $index, $selected);
            }
        }
    }

    public function selectAll(): self
    {
        $rows = $this->getRows();
        $this->selectedIndexes = array_keys($rows);

        return $this;
    }

    public function deselectAll(): self
    {
        $this->selectedIndexes = [];

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSelectedRows(): array
    {
        $rows = $this->getRows();
        $selected = [];

        foreach ($this->selectedIndexes as $index) {
            if (isset($rows[$index])) {
                $selected[] = $rows[$index];
            }
        }

        return $selected;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHighlightedRow(): ?array
    {
        $rows = $this->getRows();

        return $rows[$this->highlightedIndex] ?? null;
    }

    public function getHighlightedIndex(): int
    {
        return $this->highlightedIndex;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        if ($this->resolvedRows === null) {
            if ($this->rows instanceof Closure) {
                $this->resolvedRows = ($this->rows)();
            } else {
                $this->resolvedRows = $this->rows;
            }
        }

        return $this->resolvedRows;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function setRows(array $rows): self
    {
        $this->rows = $rows;
        $this->resolvedRows = null;

        $rowCount = count($rows);
        if ($this->highlightedIndex >= $rowCount) {
            $this->highlightedIndex = max(0, $rowCount - 1);
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function addRow(array $row): self
    {
        $rows = $this->getRows();
        $rows[] = $row;
        $this->rows = $rows;
        $this->resolvedRows = null;

        return $this;
    }

    public function removeRow(int $index): self
    {
        $rows = $this->getRows();

        if (!isset($rows[$index])) {
            return $this;
        }

        unset($rows[$index]);
        $rows = array_values($rows);
        $this->rows = $rows;
        $this->resolvedRows = null;

        $rowCount = count($rows);
        if ($this->highlightedIndex >= $rowCount && $rowCount > 0) {
            $this->highlightedIndex = $rowCount - 1;
        } elseif ($rowCount === 0) {
            $this->highlightedIndex = 0;
        }

        $this->selectedIndexes = array_values(array_filter(
            $this->selectedIndexes,
            fn (int $i) => $i !== $index && isset($rows[$i < $index ? $i : $i - 1])
        ));

        $this->selectedIndexes = array_map(
            fn (int $i) => $i > $index ? $i - 1 : $i,
            $this->selectedIndexes
        );

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateRow(int $index, array $data): self
    {
        $rows = $this->getRows();

        if (isset($rows[$index])) {
            $rows[$index] = array_merge($rows[$index], $data);
            $this->rows = $rows;
            $this->resolvedRows = null;
        }

        return $this;
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

    public function isShowBorders(): bool
    {
        return $this->showBorders;
    }

    public function isStriped(): bool
    {
        return $this->striped;
    }

    public function getHighlightStyleOption(): string
    {
        return $this->highlightStyle;
    }

    public function getEmptyText(): string
    {
        return $this->emptyText;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function isMultiSelectEnabled(): bool
    {
        return $this->multiSelectEnabled;
    }

    /**
     * @return list<int>
     */
    public function getSelectedIndexes(): array
    {
        return $this->selectedIndexes;
    }

    public function getScrollOffset(): int
    {
        return $this->scrollOffset;
    }

    public function setScrollOffset(int $offset): self
    {
        $this->scrollOffset = $offset;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    private function triggerOnHighlight(int $oldIndex): void
    {
        if ($this->onHighlight === null || $oldIndex === $this->highlightedIndex) {
            return;
        }

        $row = $this->getHighlightedRow();

        if ($row !== null) {
            ($this->onHighlight)($row, $this->highlightedIndex);
        }
    }
}
