<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class StatusBarRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof StatusBarWidget) {
            return;
        }

        $padding = $widget->getPadding();
        $availableWidth = $area->width - ($padding * 2);

        if ($availableWidth <= 0) {
            return;
        }

        $content = $this->buildContent($widget, $availableWidth);
        $displayContent = $this->truncateContent($content, $availableWidth);

        $paddedContent = str_repeat(' ', $padding) . $displayContent;
        $paddedContent = str_pad($paddedContent, $area->width);

        $buffer->putString(
            Position::at($area->position->x, $area->position->y),
            $paddedContent,
            Style::default(),
            $area->width
        );
    }

    private function buildContent(StatusBarWidget $widget, int $availableWidth): string
    {
        $leftContent = $widget->getLeft();
        $centerContent = $widget->getCenter();
        $rightContent = $widget->getRight();

        if ($leftContent !== null || $centerContent !== null || $rightContent !== null) {
            return $this->buildSectionContent($widget, $availableWidth);
        }

        $breadcrumbs = $widget->getBreadcrumbs();
        if (count($breadcrumbs) > 0) {
            $breadcrumbText = implode($widget->getBreadcrumbSeparator(), $breadcrumbs);
            $hints = $widget->getHints();

            if (count($hints) > 0) {
                $hintText = $this->buildHintContent($widget);

                return "{$breadcrumbText}  {$hintText}";
            }

            return $breadcrumbText;
        }

        return $this->buildHintContent($widget);
    }

    private function buildSectionContent(StatusBarWidget $widget, int $availableWidth): string
    {
        $left = $widget->getLeft() ?? '';
        $center = $widget->getCenter() ?? '';
        $right = $widget->getRight() ?? '';

        $leftLen = mb_strlen($left);
        $centerLen = mb_strlen($center);
        $rightLen = mb_strlen($right);

        $totalContent = $leftLen + $centerLen + $rightLen;

        if ($totalContent >= $availableWidth) {
            $parts = [];
            if ($left !== '') {
                $parts[] = $left;
            }
            if ($center !== '') {
                $parts[] = $center;
            }
            if ($right !== '') {
                $parts[] = $right;
            }

            return implode(' ', $parts);
        }

        $remainingSpace = $availableWidth - $totalContent;
        $leftGap = (int) floor($remainingSpace / 2);
        $rightGap = $remainingSpace - $leftGap;

        return $left . str_repeat(' ', $leftGap) . $center . str_repeat(' ', $rightGap) . $right;
    }

    private function buildHintContent(StatusBarWidget $widget): string
    {
        $hints = $widget->getFormattedHints();

        if (count($hints) === 0) {
            return '';
        }

        $separator = $widget->getSeparator();
        $hintStrings = [];

        foreach ($hints as $hint) {
            $hintStrings[] = "[{$hint['key']}]{$hint['action']}";
        }

        return implode($separator, $hintStrings);
    }

    private function truncateContent(string $content, int $maxWidth): string
    {
        if (mb_strlen($content) <= $maxWidth) {
            return $content;
        }

        return mb_substr($content, 0, $maxWidth);
    }
}
