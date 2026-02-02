<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\DataTable\Column;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class DataTableRenderer implements WidgetRenderer
{
    private const ELLIPSIS = "\u{2026}";
    private const SCROLL_RIGHT = "\u{2192}";
    private const SCROLL_LEFT = "\u{2190}";
    private const SORT_ASC = "\u{25B2}";
    private const SORT_DESC = "\u{25BC}";

    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof DataTableWidget) {
            return;
        }

        $data = $widget->getData();
        $columns = array_filter($widget->getColumns(), fn (Column $col) => $col->visible);

        if (count($columns) === 0 && count($data) === 0) {
            $this->renderEmptyState($buffer, $area);

            return;
        }

        $currentY = $area->position->y;
        $maxY = $area->position->y + $area->height;

        if ($widget->getTitle() !== null || $widget->isShowCount()) {
            if ($currentY < $maxY) {
                $this->renderTitleBar($buffer, $area, $widget, $currentY);
                $currentY++;
            }
        }

        $columnWidths = $this->calculateColumnWidths($columns, $area->width, $widget->isShowRowNumbers());
        $scrollCol = $widget->getScrollCol();

        if ($widget->isShowHeaders() && count($columns) > 0) {
            if ($currentY < $maxY) {
                $this->renderHeaderRow($buffer, $area, $columns, $columnWidths, $currentY, $scrollCol, $widget);
                $currentY++;
            }

            if ($currentY < $maxY) {
                $this->renderSeparatorLine($buffer, $area, $currentY);
                $currentY++;
            }
        }

        if (count($data) === 0) {
            $this->renderEmptyState($buffer, $area);

            return;
        }

        $visibleRows = $widget->getVisibleRows();
        $rowNumber = $widget->getScrollRow() + 1;

        foreach ($visibleRows as $row) {
            if ($currentY >= $maxY) {
                break;
            }

            $isStripedRow = $widget->isStriped() && ($rowNumber % 2 === 0);
            $this->renderDataRow(
                $buffer,
                $area,
                $columns,
                $columnWidths,
                $row,
                $currentY,
                $scrollCol,
                $widget->isShowRowNumbers(),
                $rowNumber,
                $isStripedRow,
                $widget
            );

            $currentY++;
            $rowNumber++;
        }

        if ($widget->isShowScrollIndicators()) {
            $this->renderScrollIndicators($buffer, $area, $widget, $columns, $scrollCol);
        }
    }

    /**
     * @param list<Column> $columns
     * @return array<int, int>
     */
    private function calculateColumnWidths(array $columns, int $availableWidth, bool $showRowNumbers): array
    {
        $widths = [];
        $rowNumberWidth = $showRowNumbers ? 4 : 0;
        $usableWidth = $availableWidth - $rowNumberWidth;

        foreach ($columns as $index => $column) {
            if ($column->width !== null) {
                $widths[$index] = $column->width;
            } else {
                $widths[$index] = max(mb_strlen($column->label), 10);
            }
        }

        return $widths;
    }

    private function renderEmptyState(Buffer $buffer, Area $area): void
    {
        $text = 'No data';
        $x = $area->position->x + (int) floor(($area->width - mb_strlen($text)) / 2);
        $y = $area->position->y + (int) floor($area->height / 2);

        $buffer->putString(
            Position::at($x, $y),
            $text,
            Style::default(),
            mb_strlen($text)
        );
    }

    private function renderTitleBar(Buffer $buffer, Area $area, DataTableWidget $widget, int $y): void
    {
        $x = $area->position->x;
        $title = $widget->getTitle() ?? '';

        if ($title !== '') {
            $buffer->putString(
                Position::at($x, $y),
                $title,
                Style::default()->addModifier(Modifier::BOLD),
                mb_strlen($title)
            );
            $x += mb_strlen($title) + 1;
        }

        if ($widget->isShowCount()) {
            $total = $widget->getTotalRows();
            $scrollRow = $widget->getScrollRow();
            $maxHeight = $widget->getMaxHeight() ?? $total;
            $from = $scrollRow + 1;
            $to = min($scrollRow + $maxHeight, $total);
            $countText = sprintf('%d-%d of %d', $from, $to, $total);

            $countX = $area->position->x + $area->width - mb_strlen($countText);
            $buffer->putString(
                Position::at($countX, $y),
                $countText,
                Style::default(),
                mb_strlen($countText)
            );
        }
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
        int $y,
        int $scrollCol,
        DataTableWidget $widget
    ): void {
        $x = $area->position->x;

        for ($i = $scrollCol; $i < count($columns); $i++) {
            if ($x >= $area->position->x + $area->width) {
                break;
            }

            $column = $columns[$i];
            $width = $columnWidths[$i] ?? 10;
            $label = $column->label;

            if ($column->sortable && $widget->getSortColumn() === $column->key) {
                $sortIndicator = $widget->getSortDirection() === 'asc' ? self::SORT_ASC : self::SORT_DESC;
                $label .= ' ' . $sortIndicator;
            }

            $text = $this->alignText($label, $width, $column->align);

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
        int $scrollCol,
        bool $showRowNumbers,
        int $rowNumber,
        bool $isStripedRow,
        DataTableWidget $widget
    ): void {
        $x = $area->position->x;

        if ($showRowNumbers) {
            $numText = str_pad((string) $rowNumber, 3, ' ', STR_PAD_LEFT) . ' ';
            $buffer->putString(
                Position::at($x, $y),
                $numText,
                Style::default(),
                4
            );
            $x += 4;
        }

        for ($i = $scrollCol; $i < count($columns); $i++) {
            if ($x >= $area->position->x + $area->width) {
                break;
            }

            $column = $columns[$i];
            $width = $columnWidths[$i] ?? 10;
            $value = $column->getValue($row);

            if (mb_strlen($value) > $width && !$column->wrap) {
                $value = mb_substr($value, 0, $width - 1) . self::ELLIPSIS;
            }

            $text = $this->alignText($value, $width, $column->align);

            $buffer->putString(
                Position::at($x, $y),
                $text,
                Style::default(),
                $width
            );

            $x += $width + 1;
        }
    }

    /**
     * @param list<Column> $columns
     */
    private function renderScrollIndicators(
        Buffer $buffer,
        Area $area,
        DataTableWidget $widget,
        array $columns,
        int $scrollCol
    ): void {
        $maxWidth = $widget->getMaxWidth();

        if ($maxWidth === null) {
            return;
        }

        $totalWidth = 0;
        foreach ($columns as $col) {
            $totalWidth += ($col->width ?? 10) + 1;
        }

        if ($scrollCol > 0) {
            $buffer->putString(
                Position::at($area->position->x, $area->position->y),
                self::SCROLL_LEFT,
                Style::default(),
                1
            );
        }

        $visibleWidth = 0;
        for ($i = $scrollCol; $i < count($columns); $i++) {
            $visibleWidth += ($columns[$i]->width ?? 10) + 1;
            if ($visibleWidth >= $maxWidth) {
                break;
            }
        }

        $hasMoreColumns = ($scrollCol + count($widget->getVisibleColumns())) < count($columns);
        if ($hasMoreColumns) {
            $x = $area->position->x + $area->width - 1;
            $buffer->putString(
                Position::at($x, $area->position->y),
                self::SCROLL_RIGHT,
                Style::default(),
                1
            );
        }
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
}
