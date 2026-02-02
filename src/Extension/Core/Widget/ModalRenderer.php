<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Extension\Core\Widget\Modal\Button;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ModalRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof ModalWidget) {
            return;
        }

        if (!$widget->isOpen()) {
            return;
        }

        if ($widget->hasOverlay()) {
            $this->renderOverlay($buffer, $area, $widget->getOverlayStyle());
        }

        $modalArea = $this->calculateModalArea($widget, $area);

        $this->renderModalBox($widget, $buffer, $modalArea, $renderer);
    }

    private function renderOverlay(Buffer $buffer, Area $area, string $style): void
    {
        $overlayStyle = Style::default()->addModifier(Modifier::DIM);

        for ($y = $area->position->y; $y < $area->position->y + $area->height; $y++) {
            for ($x = $area->position->x; $x < $area->position->x + $area->width; $x++) {
                $buffer->get(Position::at($x, $y))->setStyle($overlayStyle);
            }
        }
    }

    private function calculateModalArea(ModalWidget $widget, Area $screenArea): Area
    {
        $width = $widget->getWidth() ?? 50;
        $height = $widget->getHeight() ?? 10;

        if ($widget->getMaxWidth() !== null) {
            $width = min($width, $widget->getMaxWidth());
        }
        if ($widget->getMaxHeight() !== null) {
            $height = min($height, $widget->getMaxHeight());
        }
        if ($widget->getMinWidth() !== null) {
            $width = max($width, $widget->getMinWidth());
        }
        if ($widget->getMinHeight() !== null) {
            $height = max($height, $widget->getMinHeight());
        }

        $width = min($width, $screenArea->width);
        $height = min($height, $screenArea->height);

        if ($widget->isCentered()) {
            $x = $screenArea->position->x + (int) (($screenArea->width - $width) / 2);
            $y = $screenArea->position->y + (int) (($screenArea->height - $height) / 2);
        } else {
            $x = $widget->getPositionX() ?? $screenArea->position->x;
            $y = $widget->getPositionY() ?? $screenArea->position->y;
        }

        return Area::fromScalars($x, $y, $width, $height);
    }

    private function renderModalBox(
        ModalWidget $widget,
        Buffer $buffer,
        Area $modalArea,
        WidgetRenderer $renderer
    ): void {
        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->padding(Padding::all(1));

        if ($widget->getTitle() !== null) {
            $block = $block->titles(Title::fromString($widget->getTitle()));
        }

        $renderer->render($renderer, $block, $buffer, $modalArea);

        $innerArea = Area::fromScalars(
            $modalArea->position->x + 2,
            $modalArea->position->y + 1,
            max(0, $modalArea->width - 4),
            max(0, $modalArea->height - 2)
        );

        $this->renderBody($widget, $buffer, $innerArea, $renderer);
        $this->renderButtons($widget, $buffer, $modalArea);
    }

    private function renderBody(
        ModalWidget $widget,
        Buffer $buffer,
        Area $innerArea,
        WidgetRenderer $renderer
    ): void {
        $body = $widget->getBody();

        if ($body === null) {
            return;
        }

        if ($body instanceof Widget) {
            $renderer->render($renderer, $body, $buffer, $innerArea);

            return;
        }

        $lines = explode("\n", $body);
        $y = $innerArea->position->y;

        foreach ($lines as $line) {
            if ($y >= $innerArea->position->y + $innerArea->height - 2) {
                break;
            }

            $buffer->putString(
                Position::at($innerArea->position->x, $y),
                mb_substr($line, 0, $innerArea->width),
                Style::default(),
                $innerArea->width
            );
            $y++;
        }
    }

    private function renderButtons(
        ModalWidget $widget,
        Buffer $buffer,
        Area $modalArea
    ): void {
        $buttons = $widget->getButtons();

        if (count($buttons) === 0) {
            return;
        }

        $buttonY = $modalArea->position->y + $modalArea->height - 2;

        if ($buttonY < $modalArea->position->y) {
            return;
        }

        $buttonStrings = [];
        $totalWidth = 0;

        foreach ($buttons as $index => $button) {
            $label = $button->getLabel();
            $isFocused = ($index === $widget->getFocusedButtonIndex());

            if ($isFocused) {
                $buttonStr = "[ {$label} ]";
            } else {
                $buttonStr = "[ {$label} ]";
            }

            $buttonStrings[] = [
                'text' => $buttonStr,
                'focused' => $isFocused,
            ];
            $totalWidth += mb_strlen($buttonStr) + 2;
        }

        $startX = $modalArea->position->x + (int) (($modalArea->width - $totalWidth) / 2);
        $currentX = $startX;

        foreach ($buttonStrings as $buttonData) {
            $style = Style::default();
            if ($buttonData['focused']) {
                $style = $style->addModifier(Modifier::BOLD)->addModifier(Modifier::UNDERLINED);
            }

            $buffer->putString(
                Position::at($currentX, $buttonY),
                $buttonData['text'],
                $style,
                mb_strlen($buttonData['text'])
            );

            $currentX += mb_strlen($buttonData['text']) + 2;
        }
    }
}
