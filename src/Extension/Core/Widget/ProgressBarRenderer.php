<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ProgressBarRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof ProgressBarWidget) {
            return;
        }

        if ($area->height < 1 || $area->width < 1) {
            return;
        }

        $style = $widget->getStyle();

        if ($style === 'detailed') {
            $this->renderDetailed($widget, $buffer, $area);

            return;
        }

        if ($style === 'compact') {
            $this->renderCompact($widget, $buffer, $area);

            return;
        }

        $this->renderDefault($widget, $buffer, $area);
    }

    private function renderDefault(ProgressBarWidget $widget, Buffer $buffer, Area $area): void
    {
        $currentRow = $area->position->y;

        if ($widget->getLabel() !== '') {
            $label = $this->truncateLabel($widget->getLabel(), $area->width);
            $buffer->putString(
                Position::at($area->position->x, $currentRow),
                $label,
                Style::default(),
                $area->width
            );
            $currentRow++;
        }

        if ($currentRow >= $area->position->y + $area->height) {
            return;
        }

        $barWidth = $widget->getWidth() ?? $area->width;
        $infoText = $this->buildInfoText($widget);
        $infoWidth = mb_strlen($infoText);

        if ($infoWidth > 0) {
            $barWidth = min($barWidth, $area->width - $infoWidth - 2);
        }

        $barWidth = max(1, $barWidth);

        if ($widget->isIndeterminate()) {
            $bar = $this->buildIndeterminateBar($widget, $barWidth);
        } else {
            $bar = $this->buildDeterminateBar($widget, $barWidth);
        }

        if ($widget->hasShowBrackets()) {
            $bar = '[' . $bar . ']';
        }

        $line = $bar;

        if ($infoText !== '') {
            $line .= '  ' . $infoText;
        }

        $line = $this->truncateLabel($line, $area->width);

        $buffer->putString(
            Position::at($area->position->x, $currentRow),
            $line,
            Style::default(),
            $area->width
        );
    }

    private function renderCompact(ProgressBarWidget $widget, Buffer $buffer, Area $area): void
    {
        $infoText = '';

        if ($widget->getShowPercent()) {
            $infoText = sprintf('%d%%', (int) ($widget->getProgress() * 100));
        }

        $infoWidth = mb_strlen($infoText);
        $barWidth = $widget->getWidth() ?? ($area->width - $infoWidth - 3);
        $barWidth = max(1, $barWidth);

        $bar = $this->buildDeterminateBar($widget, $barWidth);
        $line = '[' . $bar . ']';

        if ($infoText !== '') {
            $line .= ' ' . $infoText;
        }

        $line = $this->truncateLabel($line, $area->width);

        $buffer->putString(
            Position::at($area->position->x, $area->position->y),
            $line,
            Style::default(),
            $area->width
        );
    }

    private function renderDetailed(ProgressBarWidget $widget, Buffer $buffer, Area $area): void
    {
        $currentRow = $area->position->y;

        if ($widget->getLabel() !== '') {
            $label = $this->truncateLabel($widget->getLabel(), $area->width);
            $buffer->putString(
                Position::at($area->position->x, $currentRow),
                $label,
                Style::default(),
                $area->width
            );
            $currentRow++;
        }

        if ($currentRow >= $area->position->y + $area->height) {
            return;
        }

        $barWidth = min($widget->getWidth() ?? 40, $area->width - 10);
        $barWidth = max(1, $barWidth);
        $bar = $this->buildDeterminateBar($widget, $barWidth);
        $percent = sprintf('%d%%', (int) ($widget->getProgress() * 100));

        $progressLine = "Progress: {$bar}  {$percent}";
        $buffer->putString(
            Position::at($area->position->x, $currentRow),
            $this->truncateLabel($progressLine, $area->width),
            Style::default(),
            $area->width
        );
        $currentRow++;

        if ($widget->getCurrent() !== null && $widget->getTotal() !== null) {
            if ($currentRow < $area->position->y + $area->height) {
                $countLine = sprintf(
                    'Current:  %s / %s rows',
                    number_format($widget->getCurrent()),
                    number_format($widget->getTotal())
                );
                $buffer->putString(
                    Position::at($area->position->x, $currentRow),
                    $this->truncateLabel($countLine, $area->width),
                    Style::default(),
                    $area->width
                );
                $currentRow++;
            }
        }

        if ($widget->getStartTime() !== null) {
            if ($currentRow < $area->position->y + $area->height) {
                $elapsed = $widget->getElapsed();
                $elapsedLine = sprintf('Elapsed:  %ds', $elapsed);
                $buffer->putString(
                    Position::at($area->position->x, $currentRow),
                    $this->truncateLabel($elapsedLine, $area->width),
                    Style::default(),
                    $area->width
                );
                $currentRow++;
            }

            $eta = $widget->getEta();

            if ($eta !== null && $currentRow < $area->position->y + $area->height) {
                $etaLine = sprintf('ETA:      %ds', $eta);
                $buffer->putString(
                    Position::at($area->position->x, $currentRow),
                    $this->truncateLabel($etaLine, $area->width),
                    Style::default(),
                    $area->width
                );
                $currentRow++;
            }

            $rate = $widget->getRate();

            if ($rate > 0 && $currentRow < $area->position->y + $area->height) {
                $rateLine = sprintf('Rate:     %.0f rows/s', $rate);
                $buffer->putString(
                    Position::at($area->position->x, $currentRow),
                    $this->truncateLabel($rateLine, $area->width),
                    Style::default(),
                    $area->width
                );
            }
        }
    }

    private function buildDeterminateBar(ProgressBarWidget $widget, int $width): string
    {
        $filledCount = (int) floor($widget->getProgress() * $width);
        $emptyCount = $width - $filledCount;

        $filled = str_repeat($widget->getFilledChar(), $filledCount);
        $empty = str_repeat($widget->getEmptyChar(), $emptyCount);

        return $filled . $empty;
    }

    private function buildIndeterminateBar(ProgressBarWidget $widget, int $width): string
    {
        $spinnerWidth = min(7, (int) ceil($width / 4));
        $frame = $widget->getAnimationFrame();
        $position = $frame % ($width - $spinnerWidth + 1);

        $bar = str_repeat($widget->getEmptyChar(), $width);
        $bar = mb_substr($bar, 0, $position)
            . str_repeat($widget->getFilledChar(), $spinnerWidth)
            . mb_substr($bar, $position + $spinnerWidth);

        return mb_substr($bar, 0, $width);
    }

    private function buildInfoText(ProgressBarWidget $widget): string
    {
        $parts = [];

        if ($widget->getShowPercent()) {
            $parts[] = sprintf('%d%%', (int) ($widget->getProgress() * 100));
        }

        if ($widget->getShowCount() && $widget->getCurrent() !== null && $widget->getTotal() !== null) {
            $parts[] = sprintf('%d/%d', $widget->getCurrent(), $widget->getTotal());
        }

        if ($widget->getShowEta() && $widget->getStartTime() !== null) {
            $eta = $widget->getEta();

            if ($eta !== null) {
                $parts[] = sprintf('ETA: %ds', $eta);
            }
        }

        return implode('  ', $parts);
    }

    private function truncateLabel(string $label, int $maxWidth): string
    {
        if (mb_strlen($label) <= $maxWidth) {
            return $label;
        }

        return mb_substr($label, 0, $maxWidth);
    }
}
