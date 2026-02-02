<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\SelectableTable\Column;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class SelectableTableRenderer implements WidgetRenderer
{
    private const HIGHLIGHT_SYMBOL = '> ';
    private const NO_HIGHLIGHT_SYMBOL = '  ';

    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof SelectableTableWidget) {
            return;
        }

        $rows = $widget->getRows();
        $columns = array_filter($widget->getColumns(), fn (Column $col) => $col->visible);

        if (count($columns) === 0) {
            return;
        }

        $currentY = $area->position->y;
        $maxY = $area->position->y + $area->height;

        if (count($rows) === 0) {
            $this->renderEmptyState($buffer, $area, $widget->getEmptyText());

            return;
        }

        $columnWidths = $this->calculateColumnWidths($columns, $area->width - 2);

        if ($widget->isShowHeaders()) {
            if ($currentY < $maxY) {
                $this->renderHeaderRow($buffer, $area, $columns, $columnWidths, $currentY);
                $currentY++;
            }

            if ($currentY < $maxY) {
                $this->renderSeparatorLine($buffer, $area, $currentY);
                $currentY++;
            }
        }

        $maxVisibleRows = $widget->getMaxHeight() ?? ($maxY - $currentY);
        $visibleRowCount = min(count($rows), $maxVisibleRows);

        $scrollOffset = $this->calculateScrollOffset(
            $widget->getHighlightedIndex(),
            count($rows),
            $visibleRowCount,
            $widget->getScrollOffset()
        );

        $widget->setScrollOffset($scrollOffset);

        $rowIndex = $scrollOffset;
        $renderedRows = 0;

        while ($currentY < $maxY && $rowIndex < count($rows) && $renderedRows < $visibleRowCount) {
            $row = $rows[$rowIndex];
            $isHighlighted = $rowIndex === $widget->getHighlightedIndex();
            $isSelected = in_array($rowIndex, $widget->getSelectedIndexes(), true);
            $isStripedRow = $widget->isStriped() && ($rowIndex % 2 === 1);

            $this->renderDataRow(
                $buffer,
                $area,
                $columns,
                $columnWidths,
                $row,
                $currentY,
                $isHighlighted,
                $isSelected,
                $isStripedRow,
                $widget->getHighlightStyleOption(),
                $widget->isMultiSelectEnabled()
            );

            $currentY++;
            $rowIndex++;
            $renderedRows++;
        }
    }

    /**
     * @param list<Column> $columns
     * @return array<int, int>
     */
    private function calculateColumnWidths(array $columns, int $availableWidth): array
    {
        $widths = [];
        $flexColumns = [];
        $usedWidth = 0;

        foreach ($columns as $index => $column) {
            if ($column->width !== null) {
                $widths[$index] = $column->width;
                $usedWidth += $column->width;
            } elseif ($column->flex !== null) {
                $flexColumns[$index] = $column->flex;
            } else {
                $defaultWidth = max(mb_strlen($column->label), 10);
                $widths[$index] = $defaultWidth;
                $usedWidth += $defaultWidth;
            }
        }

        $remainingWidth = max(0, $availableWidth - $usedWidth);

        if (count($flexColumns) > 0 && $remainingWidth > 0) {
            $totalFlex = array_sum($flexColumns);
            foreach ($flexColumns as $index => $flex) {
                $flexWidth = (int) floor($remainingWidth * ($flex / $totalFlex));
                $column = $columns[$index];

                if ($column->minWidth !== null) {
                    $flexWidth = max($flexWidth, $column->minWidth);
                }

                if ($column->maxWidth !== null) {
                    $flexWidth = min($flexWidth, $column->maxWidth);
                }

                $widths[$index] = $flexWidth;
            }
        }

        ksort($widths);

        return $widths;
    }

    private function renderEmptyState(Buffer $buffer, Area $area, string $text): void
    {
        $x = $area->position->x + (int) floor(($area->width - mb_strlen($text)) / 2);
        $y = $area->position->y + (int) floor($area->height / 2);

        $buffer->putString(
            Position::at($x, $y),
            $text,
            Style::default(),
            mb_strlen($text)
        );
    }

    /**
     * @param list<Column> $columns
     * @param array<int, int> $columnWidths
     */
    private function renderHeaderRow(
        Buffer $buffer,
        Area $area,
        array $columns,
        array $columnWidths,
        int $y
    ): void {
        $x = $area->position->x + mb_strlen(self::NO_HIGHLIGHT_SYMBOL);

        foreach ($columns as $index => $column) {
            if (!isset($columnWidths[$index])) {
                continue;
            }

            $width = $columnWidths[$index];
            $text = $this->alignText($column->label, $width, $column->align);

            $buffer->putString(
                Position::at($x, $y),
                $text,
                Style::default()->addModifier(Modifier::BOLD),
                $width
            );

            $x += $width + 1;
        }
    }

    private function renderSeparatorLine(Buffer $buffer, Area $area, int $y): void
    {
        $line = str_repeat("\u{2500}", $area->width);
        $buffer->putString(
            Position::at($area->position->x, $y),
            $line,
            Style::default(),
            $area->width
        );
    }

    /**
     * @param list<Column> $columns
     * @param array<int, int> $columnWidths
     * @param array<string, mixed> $row
     */
    private function renderDataRow(
        Buffer $buffer,
        Area $area,
        array $columns,
        array $columnWidths,
        array $row,
        int $y,
        bool $isHighlighted,
        bool $isSelected,
        bool $isStripedRow,
        string $highlightStyleOption,
        bool $multiSelectEnabled
    ): void {
        $x = $area->position->x;

        $prefix = $isHighlighted ? self::HIGHLIGHT_SYMBOL : self::NO_HIGHLIGHT_SYMBOL;
        $buffer->putString(
            Position::at($x, $y),
            $prefix,
            Style::default(),
            mb_strlen($prefix)
        );

        $x += mb_strlen($prefix);

        $rowStyle = $this->getRowStyle($isHighlighted, $isStripedRow, $highlightStyleOption);

        foreach ($columns as $index => $column) {
            if (!isset($columnWidths[$index])) {
                continue;
            }

            $width = $columnWidths[$index];
            $value = $column->getValue($row);

            if ($column->truncate && mb_strlen($value) > $width) {
                $value = mb_substr($value, 0, $width - 1) . "\u{2026}";
            }

            $text = $this->alignText($value, $width, $column->align);

            $buffer->putString(
                Position::at($x, $y),
                $text,
                $rowStyle,
                $width
            );

            $x += $width + 1;
        }

        if ($isHighlighted) {
            $rowArea = Area::fromScalars(
                $area->position->x,
                $y,
                $area->width,
                1
            );
            $buffer->setStyle($rowArea, $rowStyle);
        }
    }

    private function getRowStyle(bool $isHighlighted, bool $isStriped, string $highlightStyleOption): Style
    {
        $style = Style::default();

        if ($isHighlighted) {
            $style = match ($highlightStyleOption) {
                'inverse' => $style->addModifier(Modifier::REVERSED),
                'bold' => $style->addModifier(Modifier::BOLD),
                default => $style,
            };
        }

        return $style;
    }

    private function alignText(string $text, int $width, string $align): string
    {
        $textLen = mb_strlen($text);

        if ($textLen >= $width) {
            return mb_substr($text, 0, $width);
        }

        $padding = $width - $textLen;

        return match ($align) {
            'center' => str_repeat(' ', (int) floor($padding / 2)) . $text . str_repeat(' ', (int) ceil($padding / 2)),
            'right' => str_repeat(' ', $padding) . $text,
            default => $text . str_repeat(' ', $padding),
        };
    }

    private function calculateScrollOffset(
        int $highlightedIndex,
        int $totalRows,
        int $visibleRows,
        int $currentOffset
    ): int {
        if ($totalRows <= $visibleRows) {
            return 0;
        }

        if ($highlightedIndex < $currentOffset) {
            return $highlightedIndex;
        }

        if ($highlightedIndex >= $currentOffset + $visibleRows) {
            return $highlightedIndex - $visibleRows + 1;
        }

        return $currentOffset;
    }
}
