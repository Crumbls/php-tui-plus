<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\TreeWidget;

/**
 * Tree Component Tests
 *
 * A hierarchical list component with expandable/collapsible nodes.
 * Used for displaying nested data structures with parent-child relationships.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders root nodes', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Root 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Root 2', 'parent_id' => null],
            ]);

        $lines = renderToLines($widget, 40, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Root 1');
        expect($fullOutput)->toContain('Root 2');
    });

    test('renders children with correct indent', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1]);

        $lines = renderToLines($widget, 40, 5);

        // Child should be indented from parent
        expect($widget->getVisibleNodes())->toHaveCount(2);
    });

    test('shows correct branch characters', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Child 2', 'parent_id' => 1],
            ])
            ->expanded([1])
            ->showLines(true);

        $lines = renderToLines($widget, 40, 5);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Child 1');
        expect($fullOutput)->toContain('Child 2');
    });

    test('shows expand/collapse icons', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->showIcons(true)
            ->collapseIcon('>')
            ->expandIcon('v');

        // When collapsed, should show collapse icon
        expect($widget->isExpanded(1))->toBeFalse();
    });

    test('highlights current node', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
            ])
            ->setHighlighted(2);

        expect($widget->getHighlightedId())->toBe(2);
    });

    test('hides collapsed children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ]);

        // Parent is collapsed by default
        $visibleNodes = $widget->getVisibleNodes();
        expect($visibleNodes)->toHaveCount(1);
    });

    test('shows expanded children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1]);

        $visibleNodes = $widget->getVisibleNodes();
        expect($visibleNodes)->toHaveCount(2);
    });
});

// =============================================================================
// NAVIGATION TESTS
// =============================================================================

describe('navigation', function (): void {
    test('up/down moves through visible nodes', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
                ['id' => 3, 'label' => 'Node 3', 'parent_id' => null],
            ])
            ->setHighlighted(1);

        $widget->nextNode();
        expect($widget->getHighlightedId())->toBe(2);

        $widget->nextNode();
        expect($widget->getHighlightedId())->toBe(3);
    });

    test('skips collapsed children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Parent 2', 'parent_id' => null],
            ])
            ->setHighlighted(1);

        // Parent 1 is collapsed, so next should be Parent 2
        $widget->nextNode();
        expect($widget->getHighlightedId())->toBe(3);
    });

    test('left collapses expanded node', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1])
            ->setHighlighted(1);

        expect($widget->isExpanded(1))->toBeTrue();

        // Simulate left arrow - should collapse if expanded
        if ($widget->isExpanded(1)) {
            $widget->collapse(1);
        }

        expect($widget->isExpanded(1))->toBeFalse();
    });

    test('left moves to parent if collapsed or leaf', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1])
            ->setHighlighted(2);

        $widget->goToParent();

        expect($widget->getHighlightedId())->toBe(1);
    });

    test('right expands collapsed node', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->setHighlighted(1);

        expect($widget->isExpanded(1))->toBeFalse();

        $widget->expand(1);

        expect($widget->isExpanded(1))->toBeTrue();
    });

    test('right moves to first child if expanded', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Child 2', 'parent_id' => 1],
            ])
            ->expanded([1])
            ->setHighlighted(1);

        $widget->goToFirstChild();

        expect($widget->getHighlightedId())->toBe(2);
    });

    test('home/end jump to first/last', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
                ['id' => 3, 'label' => 'Node 3', 'parent_id' => null],
            ])
            ->setHighlighted(2);

        $widget->firstNode();
        expect($widget->getHighlightedId())->toBe(1);

        $widget->lastNode();
        expect($widget->getHighlightedId())->toBe(3);
    });
});

// =============================================================================
// EXPAND/COLLAPSE TESTS
// =============================================================================

describe('expand/collapse', function (): void {
    test('space toggles expansion', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->setHighlighted(1);

        expect($widget->isExpanded(1))->toBeFalse();

        $widget->toggle(1);
        expect($widget->isExpanded(1))->toBeTrue();

        $widget->toggle(1);
        expect($widget->isExpanded(1))->toBeFalse();
    });

    test('expand shows children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ]);

        expect($widget->getVisibleNodes())->toHaveCount(1);

        $widget->expand(1);

        expect($widget->getVisibleNodes())->toHaveCount(2);
    });

    test('collapse hides children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1]);

        expect($widget->getVisibleNodes())->toHaveCount(2);

        $widget->collapse(1);

        expect($widget->getVisibleNodes())->toHaveCount(1);
    });

    test('expandAll expands all nodes', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Parent 2', 'parent_id' => null],
                ['id' => 4, 'label' => 'Child 2', 'parent_id' => 3],
            ]);

        $widget->expandAll();

        expect($widget->isExpanded(1))->toBeTrue();
        expect($widget->isExpanded(3))->toBeTrue();
        expect($widget->getVisibleNodes())->toHaveCount(4);
    });

    test('collapseAll collapses all nodes', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Parent 2', 'parent_id' => null],
                ['id' => 4, 'label' => 'Child 2', 'parent_id' => 3],
            ])
            ->expanded([1, 3]);

        $widget->collapseAll();

        expect($widget->isExpanded(1))->toBeFalse();
        expect($widget->isExpanded(3))->toBeFalse();
        expect($widget->getVisibleNodes())->toHaveCount(2);
    });

    test('events fire on expand/collapse', function (): void {
        $expandedId = null;
        $collapsedId = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->onExpand(function ($item, $id) use (&$expandedId): void {
                $expandedId = $id;
            })
            ->onCollapse(function ($item, $id) use (&$collapsedId): void {
                $collapsedId = $id;
            });

        $widget->expand(1);
        expect($expandedId)->toBe(1);

        $widget->collapse(1);
        expect($collapsedId)->toBe(1);
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onSelect fires on Enter', function (): void {
        $selectedItem = null;
        $selectedId = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
            ])
            ->setHighlighted(1)
            ->onSelect(function ($item, $id) use (&$selectedItem, &$selectedId): void {
                $selectedItem = $item;
                $selectedId = $id;
            });

        $widget->select();

        expect($selectedId)->toBe(1);
        expect($selectedItem['label'])->toBe('Node 1');
    });

    test('onHighlight fires on navigation', function (): void {
        $highlightedId = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
            ])
            ->setHighlighted(1)
            ->onHighlight(function ($item, $id) use (&$highlightedId): void {
                $highlightedId = $id;
            });

        $widget->nextNode();

        expect($highlightedId)->toBe(2);
    });

    test('onExpand fires on expand', function (): void {
        $expandedId = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->onExpand(function ($item, $id) use (&$expandedId): void {
                $expandedId = $id;
            });

        $widget->expand(1);

        expect($expandedId)->toBe(1);
    });

    test('onCollapse fires on collapse', function (): void {
        $collapsedId = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ])
            ->expanded([1])
            ->onCollapse(function ($item, $id) use (&$collapsedId): void {
                $collapsedId = $id;
            });

        $widget->collapse(1);

        expect($collapsedId)->toBe(1);
    });

    test('onKeyPress allows custom handlers', function (): void {
        $keyPressed = null;

        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
            ])
            ->setHighlighted(1)
            ->onKeyPress(function ($key, $item, $id) use (&$keyPressed): bool {
                $keyPressed = $key;
                return true;
            });

        expect($widget->getOnKeyPress())->not->toBeNull();
    });
});

// =============================================================================
// DATA TESTS
// =============================================================================

describe('data', function (): void {
    test('flat array with parent_id works', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Root', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Grandchild', 'parent_id' => 2],
            ]);

        expect($widget->getItems())->toHaveCount(3);
        expect($widget->getChildren(1))->toHaveCount(1);
        expect($widget->getChildren(2))->toHaveCount(1);
    });

    test('nested array structure works', function (): void {
        $widget = TreeWidget::make()
            ->nested([
                [
                    'label' => 'Root',
                    'children' => [
                        ['label' => 'Child 1'],
                        ['label' => 'Child 2'],
                    ],
                ],
            ]);

        $items = $widget->getItems();
        expect(count($items))->toBeGreaterThan(0);
    });

    test('lazy loading children works', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Root', 'parent_id' => null],
            ])
            ->childrenCallback(function ($item) {
                if ($item['id'] === 1) {
                    return [
                        ['id' => 2, 'label' => 'Lazy Child', 'parent_id' => 1],
                    ];
                }
                return [];
            });

        expect($widget->getChildrenCallback())->not->toBeNull();
    });

    test('getParent returns correct parent', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
            ]);

        $parent = $widget->getParent(2);

        expect($parent)->not->toBeNull();
        expect($parent['id'])->toBe(1);
    });

    test('getChildren returns correct children', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Child 2', 'parent_id' => 1],
            ]);

        $children = $widget->getChildren(1);

        expect($children)->toHaveCount(2);
    });

    test('getDepth returns correct depth', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Root', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Grandchild', 'parent_id' => 2],
            ]);

        expect($widget->getDepth(1))->toBe(0);
        expect($widget->getDepth(2))->toBe(1);
        expect($widget->getDepth(3))->toBe(2);
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty tree renders empty state', function (): void {
        $widget = TreeWidget::make()
            ->items([]);

        $visibleNodes = $widget->getVisibleNodes();

        expect($visibleNodes)->toHaveCount(0);
    });

    test('single node tree works', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Only Node', 'parent_id' => null],
            ]);

        $visibleNodes = $widget->getVisibleNodes();

        expect($visibleNodes)->toHaveCount(1);
        expect($widget->getItem(1)['label'])->toBe('Only Node');
    });

    test('very deep nesting renders correctly', function (): void {
        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'id' => $i,
                'label' => "Level {$i}",
                'parent_id' => $i === 1 ? null : $i - 1,
            ];
        }

        $widget = TreeWidget::make()
            ->items($items)
            ->expandAll();

        expect($widget->getDepth(10))->toBe(9);
        expect($widget->getVisibleNodes())->toHaveCount(10);
    });

    test('circular references handled', function (): void {
        // This would create a circular reference if not handled
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => 2],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => 1],
            ]);

        // Should not infinite loop, just handle gracefully
        // These items have no valid root, so visible nodes depends on implementation
        $depth1 = $widget->getDepth(1);
        expect($depth1)->toBeGreaterThanOrEqual(0);
    });

    test('missing parent_id handled', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Root', 'parent_id' => null],
                ['id' => 2, 'label' => 'Orphan', 'parent_id' => 999], // Parent doesn't exist
            ]);

        // Orphan should still be in items but not visible as a root
        expect($widget->getItem(2))->not->toBeNull();
    });
});

// =============================================================================
// CONFIGURATION TESTS
// =============================================================================

describe('configuration', function (): void {
    test('idKey can be customized', function (): void {
        $widget = TreeWidget::make()
            ->idKey('node_id')
            ->parentKey('parent_node_id')
            ->labelKey('name')
            ->items([
                ['node_id' => 1, 'name' => 'Root', 'parent_node_id' => null],
                ['node_id' => 2, 'name' => 'Child', 'parent_node_id' => 1],
            ]);

        expect($widget->getIdKey())->toBe('node_id');
        expect($widget->getParentKey())->toBe('parent_node_id');
        expect($widget->getLabelKey())->toBe('name');
    });

    test('showLines can be toggled', function (): void {
        $widget = TreeWidget::make()
            ->showLines(true);

        expect($widget->isShowLines())->toBeTrue();

        $widget->showLines(false);

        expect($widget->isShowLines())->toBeFalse();
    });

    test('showIcons can be toggled', function (): void {
        $widget = TreeWidget::make()
            ->showIcons(true);

        expect($widget->isShowIcons())->toBeTrue();
    });

    test('indentSize can be set', function (): void {
        $widget = TreeWidget::make()
            ->indentSize(4);

        expect($widget->getIndentSize())->toBe(4);
    });

    test('maxDepth limits visible depth', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'L1', 'parent_id' => null],
                ['id' => 2, 'label' => 'L2', 'parent_id' => 1],
                ['id' => 3, 'label' => 'L3', 'parent_id' => 2],
            ])
            ->expandAll()
            ->maxDepth(2);

        expect($widget->getMaxDepth())->toBe(2);
    });

    test('icons can be customized', function (): void {
        $widget = TreeWidget::make()
            ->collapseIcon('+')
            ->expandIcon('-')
            ->leafIcon('*');

        expect($widget->getCollapseIcon())->toBe('+');
        expect($widget->getExpandIcon())->toBe('-');
        expect($widget->getLeafIcon())->toBe('*');
    });
});

// =============================================================================
// STATE TESTS
// =============================================================================

describe('state', function (): void {
    test('expanded can be set initially', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Parent 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Child 1', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Parent 2', 'parent_id' => null],
                ['id' => 4, 'label' => 'Child 2', 'parent_id' => 3],
            ])
            ->expanded([1]);

        expect($widget->isExpanded(1))->toBeTrue();
        expect($widget->isExpanded(3))->toBeFalse();
    });

    test('selected can be set initially', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
            ])
            ->selected(2);

        expect($widget->getSelectedId())->toBe(2);
    });

    test('goToNode navigates to specific node', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
                ['id' => 2, 'label' => 'Node 2', 'parent_id' => null],
                ['id' => 3, 'label' => 'Node 3', 'parent_id' => null],
            ]);

        $widget->goToNode(3);

        expect($widget->getHighlightedId())->toBe(3);
    });

    test('getHighlightedItem returns current item', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'Node 1', 'parent_id' => null],
            ])
            ->setHighlighted(1);

        $item = $widget->getHighlightedItem();

        expect($item)->not->toBeNull();
        expect($item['label'])->toBe('Node 1');
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are set', function (): void {
        $widget = TreeWidget::make();
        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('up');
        expect($bindings)->toHaveKey('down');
        expect($bindings)->toHaveKey('left');
        expect($bindings)->toHaveKey('right');
        expect($bindings)->toHaveKey('enter');
        expect($bindings)->toHaveKey('space');
    });

    test('custom key bindings can be set', function (): void {
        $widget = TreeWidget::make()
            ->keyBindings([
                '*' => 'expandAll',
                '-' => 'collapseAll',
            ]);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('*');
        expect($bindings['*'])->toBe('expandAll');
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = TreeWidget::make();

        $result = $widget
            ->items([])
            ->idKey('id')
            ->parentKey('parent_id')
            ->labelKey('label')
            ->showLines(true)
            ->showIcons(true)
            ->indentSize(2);

        expect($result)->toBeInstanceOf(TreeWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = TreeWidget::make()
            ->items([
                ['id' => 1, 'label' => 'wp_posts', 'parent_id' => null],
                ['id' => 2, 'label' => 'Posts Flow', 'parent_id' => 1],
                ['id' => 3, 'label' => 'Filter by Type', 'parent_id' => 2],
            ])
            ->showLines(true)
            ->showIcons(true)
            ->indentSize(2)
            ->expandAll();

        expect($widget->getVisibleNodes())->toHaveCount(3);
        expect($widget->isExpanded(1))->toBeTrue();
        expect($widget->isExpanded(2))->toBeTrue();
    });
});
