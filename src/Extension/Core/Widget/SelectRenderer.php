<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class SelectRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof SelectWidget) {
            return;
        }

        $currentX = $area->position->x;
        $currentY = $area->position->y;

        if ($widget->isShowLabel() && $widget->getLabel() !== null && $widget->getValue() === null) {
            $labelText = $widget->getLabel() . ': ';
            $labelWidth = $widget->getLabelWidth() > 0 ? $widget->getLabelWidth() : mb_strlen($labelText);

            $buffer->putString(
                Position::at($currentX, $currentY),
                str_pad($labelText, $labelWidth),
                Style::default(),
                $labelWidth
            );

            $currentX += $labelWidth;
        }

        $selectWidth = min($widget->getWidth(), $area->width - ($currentX - $area->position->x));

        $buffer->putString(
            Position::at($currentX, $currentY),
            '[',
            Style::default(),
            1
        );
        $currentX++;

        $displayWidth = $selectWidth - 3;
        $displayText = $this->getDisplayText($widget);

        if (mb_strlen($displayText) > $displayWidth) {
            $displayText = mb_substr($displayText, 0, $displayWidth);
        }
        $displayText = str_pad($displayText, $displayWidth);

        $style = Style::default();
        if ($widget->getValue() === null && $widget->getPlaceholder() !== null) {
            $style = $style->addModifier(Modifier::DIM);
        }

        $buffer->putString(
            Position::at($currentX, $currentY),
            $displayText,
            $style,
            $displayWidth
        );

        $currentX += $displayWidth;

        $buffer->putString(
            Position::at($currentX, $currentY),
            $widget->getIndicator(),
            Style::default(),
            1
        );
        $currentX++;

        $buffer->putString(
            Position::at($currentX, $currentY),
            ']',
            Style::default(),
            1
        );

        if ($widget->isOpen()) {
            $this->renderDropdown($widget, $buffer, $area, $currentY);
        }
    }

    private function getDisplayText(SelectWidget $widget): string
    {
        $value = $widget->getValue();

        if ($value === null) {
            return $widget->getPlaceholder() ?? '';
        }

        $options = $widget->getOptions();

        if (isset($options[$value])) {
            return $options[$value];
        }

        if (is_string($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function renderDropdown(
        SelectWidget $widget,
        Buffer $buffer,
        Area $area,
        int $headerY
    ): void {
        $dropdownX = $area->position->x;
        if ($widget->isShowLabel() && $widget->getLabelWidth() > 0) {
            $dropdownX += $widget->getLabelWidth();
        }

        $dropdownY = $headerY + 1;
        $dropdownWidth = $widget->getWidth();
        $filteredOptions = $widget->getFilteredOptions();
        $maxHeight = min($widget->getMaxHeight(), count($filteredOptions));
        $scrollOffset = $widget->getScrollOffset();
        $highlightedIndex = $widget->getHighlightedIndex();

        if ($dropdownY + $maxHeight > $area->height) {
            return;
        }

        $buffer->putString(
            Position::at($dropdownX, $dropdownY),
            str_repeat('-', $dropdownWidth),
            Style::default(),
            $dropdownWidth
        );
        $dropdownY++;

        $optionValues = array_values($filteredOptions);
        $visibleCount = 0;

        for ($i = $scrollOffset; $i < count($optionValues) && $visibleCount < $maxHeight; $i++) {
            $optionText = $optionValues[$i];
            $isHighlighted = ($i === $highlightedIndex);

            $prefix = $isHighlighted ? '> ' : '  ';
            $displayText = $prefix . $optionText;

            if (mb_strlen($displayText) > $dropdownWidth) {
                $displayText = mb_substr($displayText, 0, $dropdownWidth);
            }
            $displayText = str_pad($displayText, $dropdownWidth);

            $style = Style::default();
            if ($isHighlighted) {
                $style = $style->addModifier(Modifier::BOLD);
            }

            $buffer->putString(
                Position::at($dropdownX, $dropdownY),
                $displayText,
                $style,
                $dropdownWidth
            );

            $dropdownY++;
            $visibleCount++;
        }
    }
}
