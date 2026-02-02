<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Extension\Core\Widget\ContextPanel\Section;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ContextPanelRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof ContextPanelWidget) {
            return;
        }

        if ($widget->showsBorder()) {
            $this->renderWithBorder($widget, $buffer, $area, $renderer);
        } else {
            $this->renderContent($widget, $buffer, $area);
        }
    }

    private function renderWithBorder(
        ContextPanelWidget $widget,
        Buffer $buffer,
        Area $area,
        WidgetRenderer $renderer
    ): void {
        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->padding(Padding::horizontal(1));

        if ($widget->showsTitle() && $widget->getTitle() !== null) {
            $block = $block->titles(Title::fromString($widget->getTitle()));
        }

        $renderer->render($renderer, $block, $buffer, $area);

        $innerArea = Area::fromScalars(
            $area->position->x + 2,
            $area->position->y + 1,
            max(0, $area->width - 4),
            max(0, $area->height - 2)
        );

        $this->renderContent($widget, $buffer, $innerArea);
    }

    private function renderContent(
        ContextPanelWidget $widget,
        Buffer $buffer,
        Area $area
    ): void {
        if (!$widget->hasData()) {
            $this->renderEmptyState($widget, $buffer, $area);

            return;
        }

        $lines = $this->buildContentLines($widget);
        $scrollOffset = $widget->getScrollOffset();
        $y = $area->position->y;
        $maxY = $area->position->y + $area->height;

        for ($i = $scrollOffset; $i < count($lines) && $y < $maxY; $i++) {
            $line = $lines[$i];
            $displayLine = mb_substr($line['text'], 0, $area->width);

            $buffer->putString(
                Position::at($area->position->x, $y),
                str_pad($displayLine, $area->width),
                $line['style'] ?? Style::default(),
                $area->width
            );
            $y++;
        }
    }

    /**
     * @return list<array{text: string, style?: Style}>
     */
    private function buildContentLines(ContextPanelWidget $widget): array
    {
        $lines = [];
        $data = $widget->getData() ?? [];

        $customContent = $widget->getContent();
        if ($customContent !== null) {
            foreach (explode("\n", $customContent) as $contentLine) {
                $lines[] = ['text' => $contentLine];
            }

            return $lines;
        }

        $fields = $widget->resolveFields();
        if (count($fields) > 0) {
            $maxLabelLen = $this->calculateMaxLabelLength($fields);

            foreach ($fields as $label => $value) {
                $paddedLabel = str_pad($label, $maxLabelLen, ' ', STR_PAD_LEFT);
                $lines[] = ['text' => "{$paddedLabel}: {$value}"];
            }
        }

        $sections = $widget->getVisibleSections();

        foreach ($sections as $section) {
            if (count($lines) > 0) {
                $lines[] = ['text' => ''];
            }

            if ($section->getTitle() !== '') {
                $lines[] = ['text' => $section->getTitle()];
                $lines[] = ['text' => str_repeat("\u{2500}", mb_strlen($section->getTitle()))];
            }

            if ($section->hasComponent()) {
                $content = $section->renderContent($data);
                if ($content !== null) {
                    foreach (explode("\n", $content) as $contentLine) {
                        $lines[] = ['text' => $contentLine];
                    }
                }
            }

            if ($section->hasFields()) {
                $sectionFields = $section->resolveFields($data);
                $maxSectionLabelLen = $this->calculateMaxLabelLength($sectionFields);

                foreach ($sectionFields as $label => $value) {
                    $paddedLabel = str_pad($label, $maxSectionLabelLen, ' ', STR_PAD_LEFT);
                    $lines[] = ['text' => "{$paddedLabel}: {$value}"];
                }
            }
        }

        return $lines;
    }

    /**
     * @param array<string, string> $fields
     */
    private function calculateMaxLabelLength(array $fields): int
    {
        $maxLen = 0;

        foreach (array_keys($fields) as $label) {
            $maxLen = max($maxLen, mb_strlen($label));
        }

        return $maxLen;
    }

    private function renderEmptyState(
        ContextPanelWidget $widget,
        Buffer $buffer,
        Area $area
    ): void {
        $emptyText = $widget->getEmptyText();
        $centerY = $area->position->y + (int) ($area->height / 2);
        $centerX = $area->position->x + (int) (($area->width - mb_strlen($emptyText)) / 2);

        $buffer->putString(
            Position::at(max($area->position->x, $centerX), $centerY),
            $emptyText,
            Style::default(),
            $area->width
        );
    }
}
