<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class TreeWidget implements Widget
{
    /** @var array<int, array<string, mixed>> */
    private array $items = [];

    /** @var array<int|string, bool> */
    private array $expandedIds = [];

    private mixed $highlightedId = null;

    private mixed $selectedId = null;

    private int $scrollOffset = 0;

    private string $idKey = 'id';

    private string $parentKey = 'parent_id';

    private string $labelKey = 'label';

    private bool $showLines = true;

    private bool $showIcons = true;

    private int $indentSize = 2;

    private ?int $maxDepth = null;

    private bool $showRootNodes = true;

    private string $collapseIcon = '>';

    private string $expandIcon = 'v';

    private string $leafIcon = '-';

    private bool $wrapNavigation = true;

    private bool $expandOnSelect = false;

    private bool $collapseOnLeft = true;

    private bool $expandOnRight = true;

    private ?Closure $onSelect = null;

    private ?Closure $onHighlight = null;

    private ?Closure $onExpand = null;

    private ?Closure $onCollapse = null;

    private ?Closure $onKeyPress = null;

    private ?Closure $childrenCallback = null;

    private ?Closure $nodeRenderer = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'up' => 'previousNode',
        'down' => 'nextNode',
        'left' => 'collapseOrParent',
        'right' => 'expandOrChild',
        'enter' => 'select',
        'space' => 'toggleExpand',
        'home' => 'firstNode',
        'end' => 'lastNode',
    ];

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function items(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $nested
     */
    public function nested(array $nested): self
    {
        $this->items = $this->flattenNested($nested);

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $nested
     * @return array<int, array<string, mixed>>
     */
    private function flattenNested(array $nested, mixed $parentId = null, int &$autoId = 1): array
    {
        $result = [];

        foreach ($nested as $node) {
            $id = $node['id'] ?? $autoId++;
            $item = $node;
            $item[$this->idKey] = $id;
            $item[$this->parentKey] = $parentId;

            $children = $node['children'] ?? [];
            unset($item['children']);

            $result[] = $item;

            if (count($children) > 0) {
                $result = array_merge($result, $this->flattenNested($children, $id, $autoId));
            }
        }

        return $result;
    }

    public function idKey(string $key): self
    {
        $this->idKey = $key;

        return $this;
    }

    public function parentKey(string $key): self
    {
        $this->parentKey = $key;

        return $this;
    }

    public function labelKey(string $key): self
    {
        $this->labelKey = $key;

        return $this;
    }

    public function showLines(bool $show = true): self
    {
        $this->showLines = $show;

        return $this;
    }

    public function showIcons(bool $show = true): self
    {
        $this->showIcons = $show;

        return $this;
    }

    public function indentSize(int $chars): self
    {
        $this->indentSize = $chars;

        return $this;
    }

    public function maxDepth(int $depth): self
    {
        $this->maxDepth = $depth;

        return $this;
    }

    public function showRootNodes(bool $show = true): self
    {
        $this->showRootNodes = $show;

        return $this;
    }

    public function collapseIcon(string $icon): self
    {
        $this->collapseIcon = $icon;

        return $this;
    }

    public function expandIcon(string $icon): self
    {
        $this->expandIcon = $icon;

        return $this;
    }

    public function leafIcon(string $icon): self
    {
        $this->leafIcon = $icon;

        return $this;
    }

    /**
     * @param array<int|string> $ids
     */
    public function expanded(array $ids): self
    {
        $this->expandedIds = [];
        foreach ($ids as $id) {
            $this->expandedIds[$id] = true;
        }

        return $this;
    }

    public function expandAll(): self
    {
        foreach ($this->items as $item) {
            $id = $item[$this->idKey] ?? null;
            if ($id !== null && $this->hasChildren($id)) {
                $this->expandedIds[$id] = true;
            }
        }

        return $this;
    }

    public function collapseAll(): self
    {
        $this->expandedIds = [];

        return $this;
    }

    public function selected(mixed $id): self
    {
        $this->selectedId = $id;

        return $this;
    }

    public function wrapNavigation(bool $wrap = true): self
    {
        $this->wrapNavigation = $wrap;

        return $this;
    }

    public function expandOnSelect(bool $expand = false): self
    {
        $this->expandOnSelect = $expand;

        return $this;
    }

    public function collapseOnLeft(bool $collapse = true): self
    {
        $this->collapseOnLeft = $collapse;

        return $this;
    }

    public function expandOnRight(bool $expand = true): self
    {
        $this->expandOnRight = $expand;

        return $this;
    }

    public function onSelect(Closure $fn): self
    {
        $this->onSelect = $fn;

        return $this;
    }

    public function onHighlight(Closure $fn): self
    {
        $this->onHighlight = $fn;

        return $this;
    }

    public function onExpand(Closure $fn): self
    {
        $this->onExpand = $fn;

        return $this;
    }

    public function onCollapse(Closure $fn): self
    {
        $this->onCollapse = $fn;

        return $this;
    }

    public function onKeyPress(Closure $fn): self
    {
        $this->onKeyPress = $fn;

        return $this;
    }

    public function childrenCallback(Closure $fn): self
    {
        $this->childrenCallback = $fn;

        return $this;
    }

    public function nodeRenderer(Closure $fn): self
    {
        $this->nodeRenderer = $fn;

        return $this;
    }

    /**
     * @param array<string, string> $bindings
     */
    public function keyBindings(array $bindings): self
    {
        $this->keyBindings = array_merge($this->keyBindings, $bindings);

        return $this;
    }

    public function previousNode(): self
    {
        $visibleNodes = $this->getVisibleNodes();

        if (count($visibleNodes) === 0) {
            return $this;
        }

        $currentIndex = $this->getVisibleIndex($this->highlightedId);
        $oldId = $this->highlightedId;

        if ($currentIndex > 0) {
            $this->highlightedId = $visibleNodes[$currentIndex - 1][$this->idKey];
        } elseif ($this->wrapNavigation) {
            $this->highlightedId = $visibleNodes[count($visibleNodes) - 1][$this->idKey];
        }

        $this->triggerOnHighlight($oldId);

        return $this;
    }

    public function nextNode(): self
    {
        $visibleNodes = $this->getVisibleNodes();

        if (count($visibleNodes) === 0) {
            return $this;
        }

        $currentIndex = $this->getVisibleIndex($this->highlightedId);
        $oldId = $this->highlightedId;

        if ($currentIndex < count($visibleNodes) - 1) {
            $this->highlightedId = $visibleNodes[$currentIndex + 1][$this->idKey];
        } elseif ($this->wrapNavigation) {
            $this->highlightedId = $visibleNodes[0][$this->idKey];
        }

        $this->triggerOnHighlight($oldId);

        return $this;
    }

    public function firstNode(): self
    {
        $visibleNodes = $this->getVisibleNodes();

        if (count($visibleNodes) === 0) {
            return $this;
        }

        $oldId = $this->highlightedId;
        $this->highlightedId = $visibleNodes[0][$this->idKey];
        $this->triggerOnHighlight($oldId);

        return $this;
    }

    public function lastNode(): self
    {
        $visibleNodes = $this->getVisibleNodes();

        if (count($visibleNodes) === 0) {
            return $this;
        }

        $oldId = $this->highlightedId;
        $this->highlightedId = $visibleNodes[count($visibleNodes) - 1][$this->idKey];
        $this->triggerOnHighlight($oldId);

        return $this;
    }

    public function goToNode(mixed $id): self
    {
        $item = $this->getItem($id);

        if ($item !== null) {
            $oldId = $this->highlightedId;
            $this->highlightedId = $id;
            $this->triggerOnHighlight($oldId);
        }

        return $this;
    }

    public function goToParent(): self
    {
        if ($this->highlightedId === null) {
            return $this;
        }

        $parent = $this->getParent($this->highlightedId);

        if ($parent !== null) {
            $oldId = $this->highlightedId;
            $this->highlightedId = $parent[$this->idKey];
            $this->triggerOnHighlight($oldId);
        }

        return $this;
    }

    public function goToFirstChild(): self
    {
        if ($this->highlightedId === null) {
            return $this;
        }

        $children = $this->getChildren($this->highlightedId);

        if (count($children) > 0) {
            $oldId = $this->highlightedId;
            $this->highlightedId = $children[0][$this->idKey];
            $this->triggerOnHighlight($oldId);
        }

        return $this;
    }

    public function expand(mixed $id): self
    {
        if ($this->hasChildren($id)) {
            $this->expandedIds[$id] = true;

            if ($this->onExpand !== null) {
                $item = $this->getItem($id);
                if ($item !== null) {
                    ($this->onExpand)($item, $id);
                }
            }
        }

        return $this;
    }

    public function collapse(mixed $id): self
    {
        unset($this->expandedIds[$id]);

        if ($this->onCollapse !== null) {
            $item = $this->getItem($id);
            if ($item !== null) {
                ($this->onCollapse)($item, $id);
            }
        }

        return $this;
    }

    public function toggle(mixed $id): self
    {
        if ($this->isExpanded($id)) {
            $this->collapse($id);
        } else {
            $this->expand($id);
        }

        return $this;
    }

    public function expandToDepth(int $depth): self
    {
        foreach ($this->items as $item) {
            $id = $item[$this->idKey] ?? null;
            if ($id !== null && $this->getDepth($id) < $depth && $this->hasChildren($id)) {
                $this->expandedIds[$id] = true;
            }
        }

        return $this;
    }

    public function isExpanded(mixed $id): bool
    {
        return isset($this->expandedIds[$id]);
    }

    public function select(): void
    {
        if ($this->onSelect === null || $this->highlightedId === null) {
            return;
        }

        $item = $this->getHighlightedItem();

        if ($item !== null) {
            ($this->onSelect)($item, $this->highlightedId);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHighlightedItem(): ?array
    {
        if ($this->highlightedId === null) {
            return null;
        }

        return $this->getItem($this->highlightedId);
    }

    public function getHighlightedId(): mixed
    {
        return $this->highlightedId;
    }

    public function setHighlighted(mixed $id): self
    {
        $this->highlightedId = $id;

        return $this;
    }

    public function getSelectedId(): mixed
    {
        return $this->selectedId;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getItem(mixed $id): ?array
    {
        foreach ($this->items as $item) {
            if (($item[$this->idKey] ?? null) === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChildren(mixed $parentId): array
    {
        $children = [];

        foreach ($this->items as $item) {
            if (($item[$this->parentKey] ?? null) === $parentId) {
                $children[] = $item;
            }
        }

        return $children;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getParent(mixed $id): ?array
    {
        $item = $this->getItem($id);

        if ($item === null) {
            return null;
        }

        $parentId = $item[$this->parentKey] ?? null;

        if ($parentId === null) {
            return null;
        }

        return $this->getItem($parentId);
    }

    public function getDepth(mixed $id): int
    {
        $depth = 0;
        $visited = [];
        $currentId = $id;

        while ($currentId !== null && !isset($visited[$currentId])) {
            $visited[$currentId] = true;
            $parent = $this->getParent($currentId);

            if ($parent === null) {
                break;
            }

            $depth++;
            $currentId = $parent[$this->idKey] ?? null;
        }

        return $depth;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVisibleNodes(): array
    {
        $rootNodes = $this->getRootNodes();
        $visible = [];

        foreach ($rootNodes as $node) {
            $this->collectVisibleNodes($node, $visible, 0);
        }

        return $visible;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRootNodes(): array
    {
        $roots = [];

        foreach ($this->items as $item) {
            $parentId = $item[$this->parentKey] ?? null;
            if ($parentId === null || $this->getItem($parentId) === null) {
                // Only include as root if parent_id is null
                // Orphans with missing parents are not shown as roots
                if ($parentId === null) {
                    $roots[] = $item;
                }
            }
        }

        return $roots;
    }

    /**
     * @param array<string, mixed> $node
     * @param array<int, array<string, mixed>> $visible
     */
    private function collectVisibleNodes(array $node, array &$visible, int $depth): void
    {
        if ($this->maxDepth !== null && $depth >= $this->maxDepth) {
            return;
        }

        $visible[] = $node;
        $id = $node[$this->idKey] ?? null;

        if ($id !== null && $this->isExpanded($id)) {
            $children = $this->getChildren($id);
            foreach ($children as $child) {
                $this->collectVisibleNodes($child, $visible, $depth + 1);
            }
        }
    }

    public function hasChildren(mixed $id): bool
    {
        if ($this->childrenCallback !== null) {
            return true;
        }

        return count($this->getChildren($id)) > 0;
    }

    private function getVisibleIndex(mixed $id): int
    {
        $visibleNodes = $this->getVisibleNodes();

        foreach ($visibleNodes as $index => $node) {
            if (($node[$this->idKey] ?? null) === $id) {
                return $index;
            }
        }

        return 0;
    }

    private function triggerOnHighlight(mixed $oldId): void
    {
        if ($this->onHighlight === null || $oldId === $this->highlightedId) {
            return;
        }

        $item = $this->getHighlightedItem();

        if ($item !== null) {
            ($this->onHighlight)($item, $this->highlightedId);
        }
    }

    public function getIdKey(): string
    {
        return $this->idKey;
    }

    public function getParentKey(): string
    {
        return $this->parentKey;
    }

    public function getLabelKey(): string
    {
        return $this->labelKey;
    }

    public function isShowLines(): bool
    {
        return $this->showLines;
    }

    public function isShowIcons(): bool
    {
        return $this->showIcons;
    }

    public function getIndentSize(): int
    {
        return $this->indentSize;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    public function getCollapseIcon(): string
    {
        return $this->collapseIcon;
    }

    public function getExpandIcon(): string
    {
        return $this->expandIcon;
    }

    public function getLeafIcon(): string
    {
        return $this->leafIcon;
    }

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    public function getOnKeyPress(): ?Closure
    {
        return $this->onKeyPress;
    }

    public function getChildrenCallback(): ?Closure
    {
        return $this->childrenCallback;
    }

    public function getNodeRenderer(): ?Closure
    {
        return $this->nodeRenderer;
    }

    public function getScrollOffset(): int
    {
        return $this->scrollOffset;
    }

    public function setScrollOffset(int $offset): self
    {
        $this->scrollOffset = $offset;

        return $this;
    }
}
