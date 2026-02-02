<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ToastRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if ($widget instanceof ToastWidget) {
            $this->renderToast($widget, $buffer, $area);

            return;
        }

        if ($widget instanceof ToastManager) {
            $this->renderManager($renderer, $widget, $buffer, $area);

            return;
        }
    }

    private function renderToast(ToastWidget $widget, Buffer $buffer, Area $area): void
    {
        if ($area->height < 1 || $area->width < 1) {
            return;
        }

        $icon = $widget->getIcon();
        $message = $widget->getMessage();
        $action = $widget->getAction();

        $content = "{$icon} {$message}";

        if ($action !== null) {
            $content .= "  [{$action['label']}]";
        }

        $content = $this->truncate($content, $area->width - 4);

        $boxWidth = min(mb_strlen($content) + 4, $area->width);

        $topBorder = "\u{256D}" . str_repeat("\u{2500}", $boxWidth - 2) . "\u{256E}";
        $bottomBorder = "\u{2570}" . str_repeat("\u{2500}", $boxWidth - 2) . "\u{256F}";

        $paddedContent = "\u{2502} " . str_pad($content, $boxWidth - 4) . " \u{2502}";

        $y = $area->position->y;

        $buffer->putString(
            Position::at($area->position->x, $y),
            $this->truncate($topBorder, $area->width),
            Style::default(),
            $area->width
        );

        if ($y + 1 < $area->position->y + $area->height) {
            $buffer->putString(
                Position::at($area->position->x, $y + 1),
                $this->truncate($paddedContent, $area->width),
                Style::default(),
                $area->width
            );
        }

        if ($y + 2 < $area->position->y + $area->height) {
            $buffer->putString(
                Position::at($area->position->x, $y + 2),
                $this->truncate($bottomBorder, $area->width),
                Style::default(),
                $area->width
            );
        }
    }

    private function renderManager(
        WidgetRenderer $renderer,
        ToastManager $manager,
        Buffer $buffer,
        Area $area,
    ): void {
        $visible = $manager->getVisible();

        if (count($visible) === 0) {
            return;
        }

        $toastHeight = 3;
        $currentY = $area->position->y;

        if ($manager->getPosition() === 'bottom') {
            $currentY = $area->position->y + $area->height - ($toastHeight * count($visible));
        }

        foreach ($visible as $toast) {
            if ($currentY + $toastHeight > $area->position->y + $area->height) {
                break;
            }

            $toastArea = Area::fromScalars(
                $area->position->x,
                $currentY,
                $area->width,
                $toastHeight
            );

            $this->renderToast($toast, $buffer, $toastArea);

            $currentY += $toastHeight + 1;
        }
    }

    private function truncate(string $text, int $maxWidth): string
    {
        if (mb_strlen($text) <= $maxWidth) {
            return $text;
        }

        return mb_substr($text, 0, $maxWidth);
    }
}
