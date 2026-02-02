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

final class TreeRenderer implements WidgetRenderer
{
    private const BRANCH_VERTICAL = "\u{2502}   ";
    private const BRANCH_TEE = "\u{251C}\u{2500}\u{2500} ";
    private const BRANCH_CORNER = "\u{2514}\u{2500}\u{2500} ";
    private const BRANCH_EMPTY = "    ";

    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof TreeWidget) {
            return;
        }

        $visibleNodes = $widget->getVisibleNodes();

        if (count($visibleNodes) === 0) {
            return;
        }

        $currentY = $area->position->y;
        $maxY = $area->position->y + $area->height;

        foreach ($visibleNodes as $node) {
            if ($currentY >= $maxY) {
                break;
            }

            $id = $node[$widget->getIdKey()] ?? null;
            $label = (string) ($node[$widget->getLabelKey()] ?? '');
            $depth = $widget->getDepth($id);
            $isHighlighted = $id === $widget->getHighlightedId();
            $hasChildren = $widget->hasChildren($id);
            $isExpanded = $widget->isExpanded($id);

            $line = $this->buildNodeLine(
                $widget,
                $label,
                $depth,
                $hasChildren,
                $isExpanded,
                $this->isLastSibling($widget, $node),
                $this->getAncestorLastFlags($widget, $node)
            );

            $style = $isHighlighted
                ? Style::default()->addModifier(Modifier::REVERSED)
                : Style::default();

            $buffer->putString(
                Position::at($area->position->x, $currentY),
                $line,
                $style,
                $area->width
            );

            if ($isHighlighted) {
                $rowArea = Area::fromScalars(
                    $area->position->x,
                    $currentY,
                    $area->width,
                    1
                );
                $buffer->setStyle($rowArea, $style);
            }

            $currentY++;
        }
    }

    /**
     * @param array<int, bool> $ancestorLastFlags
     */
    private function buildNodeLine(
        TreeWidget $widget,
        string $label,
        int $depth,
        bool $hasChildren,
        bool $isExpanded,
        bool $isLast,
        array $ancestorLastFlags
    ): string {
        $line = '';

        if ($widget->isShowLines()) {
            for ($i = 0; $i < $depth; $i++) {
                $ancestorIsLast = $ancestorLastFlags[$i] ?? false;
                $line .= $ancestorIsLast ? self::BRANCH_EMPTY : self::BRANCH_VERTICAL;
            }

            if ($depth > 0) {
                $line = mb_substr($line, 0, -4);
                $line .= $isLast ? self::BRANCH_CORNER : self::BRANCH_TEE;
            }
        } else {
            $line .= str_repeat(' ', $depth * $widget->getIndentSize());
        }

        if ($widget->isShowIcons() && $hasChildren) {
            $line .= $isExpanded ? $widget->getExpandIcon() : $widget->getCollapseIcon();
            $line .= ' ';
        } elseif ($widget->isShowIcons()) {
            $line .= $widget->getLeafIcon() . ' ';
        }

        $line .= $label;

        return $line;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function isLastSibling(TreeWidget $widget, array $node): bool
    {
        $parentId = $node[$widget->getParentKey()] ?? null;
        $siblings = $parentId === null
            ? $this->getRootNodes($widget)
            : $widget->getChildren($parentId);

        if (count($siblings) === 0) {
            return true;
        }

        $lastSibling = $siblings[count($siblings) - 1];

        return ($lastSibling[$widget->getIdKey()] ?? null) === ($node[$widget->getIdKey()] ?? null);
    }

    /**
     * @param array<string, mixed> $node
     * @return array<int, bool>
     */
    private function getAncestorLastFlags(TreeWidget $widget, array $node): array
    {
        $flags = [];
        $current = $node;
        $depth = 0;

        while (true) {
            $parentId = $current[$widget->getParentKey()] ?? null;
            if ($parentId === null) {
                break;
            }

            $parent = $widget->getItem($parentId);
            if ($parent === null) {
                break;
            }

            $isLast = $this->isLastSibling($widget, $parent);
            array_unshift($flags, $isLast);
            $current = $parent;
            $depth++;

            if ($depth > 100) {
                break;
            }
        }

        return $flags;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRootNodes(TreeWidget $widget): array
    {
        $roots = [];
        $items = $widget->getItems();

        foreach ($items as $item) {
            $parentId = $item[$widget->getParentKey()] ?? null;
            if ($parentId === null) {
                $roots[] = $item;
            }
        }

        return $roots;
    }
}
