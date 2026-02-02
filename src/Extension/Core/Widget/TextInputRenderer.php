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

final class TextInputRenderer implements WidgetRenderer
{
    private const MASK_CHAR = "\u{2022}";

    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof TextInputWidget) {
            return;
        }

        $currentX = $area->position->x;
        $currentY = $area->position->y;

        if ($widget->isShowLabel() && $widget->getLabel() !== null) {
            $label = $widget->getLabel() . ': ';
            $labelWidth = $widget->getLabelWidth() > 0 ? $widget->getLabelWidth() : mb_strlen($label);

            $buffer->putString(
                Position::at($currentX, $currentY),
                str_pad($label, $labelWidth),
                Style::default(),
                $labelWidth
            );

            $currentX += $labelWidth;
        }

        $inputWidth = min($widget->getWidth(), $area->width - ($currentX - $area->position->x));

        $buffer->putString(
            Position::at($currentX, $currentY),
            '[',
            Style::default(),
            1
        );
        $currentX++;

        $displayWidth = $inputWidth - 2;
        $value = $widget->getValue();

        if ($widget->isPassword() && $value !== '') {
            $value = str_repeat(self::MASK_CHAR, mb_strlen($value));
        }

        if ($value === '' && $widget->getPlaceholder() !== null) {
            $placeholder = $widget->getPlaceholder();
            if (mb_strlen($placeholder) > $displayWidth) {
                $placeholder = mb_substr($placeholder, 0, $displayWidth);
            }
            $placeholder = str_pad($placeholder, $displayWidth);

            $buffer->putString(
                Position::at($currentX, $currentY),
                $placeholder,
                Style::default()->addModifier(Modifier::DIM),
                $displayWidth
            );
        } else {
            $scrollOffset = $this->calculateScrollOffset(
                $widget->getCursorPosition(),
                mb_strlen($value),
                $displayWidth,
                $widget->getScrollOffset()
            );
            $widget->setScrollOffset($scrollOffset);

            $displayValue = mb_substr($value, $scrollOffset, $displayWidth);
            $displayValue = str_pad($displayValue, $displayWidth);

            $buffer->putString(
                Position::at($currentX, $currentY),
                $displayValue,
                Style::default(),
                $displayWidth
            );
        }

        $currentX += $displayWidth;

        $buffer->putString(
            Position::at($currentX, $currentY),
            ']',
            Style::default(),
            1
        );
    }

    private function calculateScrollOffset(
        int $cursorPosition,
        int $valueLength,
        int $displayWidth,
        int $currentOffset
    ): int {
        if ($valueLength <= $displayWidth) {
            return 0;
        }

        if ($cursorPosition < $currentOffset) {
            return $cursorPosition;
        }

        if ($cursorPosition >= $currentOffset + $displayWidth) {
            return $cursorPosition - $displayWidth + 1;
        }

        return $currentOffset;
    }
}
