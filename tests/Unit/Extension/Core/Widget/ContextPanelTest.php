<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\ContextPanelWidget;
use Crumbls\Tui\Extension\Core\Widget\ContextPanel\Section;

/**
 * ContextPanel Component Tests
 *
 * A panel that displays detailed information about a currently selected item.
 * Typically positioned to the side or bottom of a list/table, updating
 * dynamically as selection changes.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders title correctly', function (): void {
        $widget = ContextPanelWidget::make()
            ->title('Details')
            ->data(['name' => 'Test Item']);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Details');
    });

    test('renders dynamic title from closure', function (): void {
        $widget = ContextPanelWidget::make()
            ->title(fn($data) => $data['name'] ?? 'Details')
            ->data(['name' => 'Transform Details']);

        expect($widget->getTitle())->toBe('Transform Details');
    });

    test('renders fields with labels and values', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Status' => fn($data) => $data['status'],
                'Count' => fn($data) => (string) $data['count'],
            ])
            ->data(['status' => 'Active', 'count' => 42]);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Status');
        expect($output)->toContain('Active');
        expect($output)->toContain('Count');
        expect($output)->toContain('42');
    });

    test('renders sections with headers', function (): void {
        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('info')
                    ->title('Information')
                    ->fields([
                        'Name' => fn($data) => $data['name'],
                    ]),
            ])
            ->data(['name' => 'Test']);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Information');
        expect($output)->toContain('Name');
        expect($output)->toContain('Test');
    });

    test('shows empty state when no data', function (): void {
        $widget = ContextPanelWidget::make()
            ->emptyText('No item selected');

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('No item selected');
    });

    test('scrolls when content exceeds height', function (): void {
        $widget = ContextPanelWidget::make()
            ->height(5)
            ->scrollable(true)
            ->fields([
                'Field 1' => fn($data) => 'Value 1',
                'Field 2' => fn($data) => 'Value 2',
                'Field 3' => fn($data) => 'Value 3',
                'Field 4' => fn($data) => 'Value 4',
                'Field 5' => fn($data) => 'Value 5',
                'Field 6' => fn($data) => 'Value 6',
                'Field 7' => fn($data) => 'Value 7',
            ])
            ->data(['dummy' => true]);

        expect($widget->canScroll())->toBeTrue();
    });

    test('shows scroll indicator when scrollable', function (): void {
        $widget = ContextPanelWidget::make()
            ->height(3)
            ->scrollable(true)
            ->fields([
                'Field 1' => fn($data) => 'Value 1',
                'Field 2' => fn($data) => 'Value 2',
                'Field 3' => fn($data) => 'Value 3',
                'Field 4' => fn($data) => 'Value 4',
                'Field 5' => fn($data) => 'Value 5',
            ])
            ->data(['dummy' => true]);

        expect($widget->isScrollable())->toBeTrue();
    });
});

// =============================================================================
// DATA TESTS
// =============================================================================

describe('data', function (): void {
    test('setData updates displayed content', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Name' => fn($data) => $data['name'],
            ]);

        expect($widget->hasData())->toBeFalse();

        $widget->setData(['name' => 'Updated']);

        expect($widget->hasData())->toBeTrue();
        expect($widget->getData()['name'])->toBe('Updated');
    });

    test('callback functions receive correct data', function (): void {
        $receivedData = null;

        $widget = ContextPanelWidget::make()
            ->fields([
                'Name' => function ($data) use (&$receivedData) {
                    $receivedData = $data;
                    return $data['name'];
                },
            ])
            ->data(['name' => 'Test', 'extra' => 'value']);

        // Trigger rendering to execute callback
        renderToLines($widget, 30, 10);

        expect($receivedData)->toBe(['name' => 'Test', 'extra' => 'value']);
    });

    test('missing data fields handled gracefully', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Name' => fn($data) => $data['name'] ?? 'N/A',
                'Missing' => fn($data) => $data['nonexistent'] ?? '-',
            ])
            ->data(['name' => 'Test']);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Test');
        expect($output)->toContain('-');
    });

    test('clearData shows empty state', function (): void {
        $widget = ContextPanelWidget::make()
            ->emptyText('Nothing selected')
            ->data(['name' => 'Test']);

        expect($widget->hasData())->toBeTrue();

        $widget->clearData();

        expect($widget->hasData())->toBeFalse();

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Nothing selected');
    });

    test('updateField updates single field', function (): void {
        $widget = ContextPanelWidget::make()
            ->data(['name' => 'Original', 'status' => 'pending']);

        $widget->updateField('status', 'completed');

        expect($widget->getData()['name'])->toBe('Original');
        expect($widget->getData()['status'])->toBe('completed');
    });
});

// =============================================================================
// SECTION TESTS
// =============================================================================

describe('sections', function (): void {
    test('sections render in order', function (): void {
        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('first')
                    ->title('First Section')
                    ->fields(['A' => fn($d) => 'a']),
                Section::make('second')
                    ->title('Second Section')
                    ->fields(['B' => fn($d) => 'b']),
                Section::make('third')
                    ->title('Third Section')
                    ->fields(['C' => fn($d) => 'c']),
            ])
            ->data(['dummy' => true]);

        $sections = $widget->getSections();

        expect($sections[0]->getId())->toBe('first');
        expect($sections[1]->getId())->toBe('second');
        expect($sections[2]->getId())->toBe('third');
    });

    test('section visibility respects condition', function (): void {
        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('always')
                    ->title('Always Visible')
                    ->fields(['A' => fn($d) => 'a']),
                Section::make('conditional')
                    ->title('Conditional')
                    ->visible(fn($data) => $data['showSection'] ?? false)
                    ->fields(['B' => fn($d) => 'b']),
            ])
            ->data(['showSection' => false]);

        $visibleSections = $widget->getVisibleSections();

        expect(count($visibleSections))->toBe(1);
        expect($visibleSections[0]->getId())->toBe('always');
    });

    test('section titles render correctly', function (): void {
        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('overview')
                    ->title('Overview')
                    ->fields(['Name' => fn($d) => $d['name']]),
            ])
            ->data(['name' => 'Test']);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Overview');
    });

    test('custom section components render', function (): void {
        $componentRendered = false;

        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('custom')
                    ->title('Custom')
                    ->component(function ($data) use (&$componentRendered) {
                        $componentRendered = true;
                        return 'Custom content';
                    }),
            ])
            ->data(['dummy' => true]);

        $section = $widget->getSections()[0];
        $section->renderContent($widget->getData());

        expect($componentRendered)->toBeTrue();
    });
});

// =============================================================================
// BINDING TESTS
// =============================================================================

describe('binding', function (): void {
    test('bind() stores source component reference', function (): void {
        $mockSource = new class {
            public function getId(): string
            {
                return 'mock-source';
            }
        };

        $widget = ContextPanelWidget::make()
            ->bind($mockSource, 'onHighlight');

        expect($widget->getBoundSource())->toBe($mockSource);
        expect($widget->getBoundEvent())->toBe('onHighlight');
    });

    test('panel can be updated via setData from external source', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Name' => fn($data) => $data['name'],
            ]);

        // Simulating what would happen when bound source triggers update
        $widget->setData(['name' => 'From source']);

        expect($widget->getData()['name'])->toBe('From source');
    });
});

// =============================================================================
// SCROLLING TESTS
// =============================================================================

describe('scrolling', function (): void {
    test('scrollUp moves viewport up', function (): void {
        $widget = ContextPanelWidget::make()
            ->scrollable(true)
            ->data(['dummy' => true]);

        $widget->scrollDown(5);
        expect($widget->getScrollOffset())->toBe(5);

        $widget->scrollUp(2);
        expect($widget->getScrollOffset())->toBe(3);
    });

    test('scrollDown moves viewport down', function (): void {
        $widget = ContextPanelWidget::make()
            ->scrollable(true)
            ->data(['dummy' => true]);

        $widget->scrollDown(3);

        expect($widget->getScrollOffset())->toBe(3);
    });

    test('scrollToTop resets to beginning', function (): void {
        $widget = ContextPanelWidget::make()
            ->scrollable(true)
            ->data(['dummy' => true]);

        $widget->scrollDown(10);
        $widget->scrollToTop();

        expect($widget->getScrollOffset())->toBe(0);
    });

    test('scrollToBottom goes to end', function (): void {
        $widget = ContextPanelWidget::make()
            ->height(5)
            ->scrollable(true)
            ->fields([
                'F1' => fn($d) => '1',
                'F2' => fn($d) => '2',
                'F3' => fn($d) => '3',
                'F4' => fn($d) => '4',
                'F5' => fn($d) => '5',
                'F6' => fn($d) => '6',
                'F7' => fn($d) => '7',
                'F8' => fn($d) => '8',
                'F9' => fn($d) => '9',
                'F10' => fn($d) => '10',
            ])
            ->data(['dummy' => true]);

        $widget->scrollToBottom();

        expect($widget->getScrollOffset())->toBeGreaterThan(0);
    });

    test('scrollUp cannot go below zero', function (): void {
        $widget = ContextPanelWidget::make()
            ->scrollable(true)
            ->data(['dummy' => true]);

        $widget->scrollUp(10);

        expect($widget->getScrollOffset())->toBe(0);
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('very long values truncate or wrap', function (): void {
        $widget = ContextPanelWidget::make()
            ->width(20)
            ->fields([
                'Long' => fn($data) => 'This is a very long value that should be handled',
            ])
            ->data(['dummy' => true]);

        $lines = renderToLines($widget, 20, 10);

        // Should render without error, content may be truncated
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('null field values handled', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Nullable' => fn($data) => $data['nullable'],
            ])
            ->data(['nullable' => null]);

        $lines = renderToLines($widget, 30, 10);

        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('empty sections do not render', function (): void {
        $widget = ContextPanelWidget::make()
            ->sections([
                Section::make('empty')
                    ->title('Empty')
                    ->fields([]),
                Section::make('filled')
                    ->title('Filled')
                    ->fields(['A' => fn($d) => 'value']),
            ])
            ->data(['dummy' => true]);

        $visibleSections = $widget->getVisibleSections();

        // Empty section should be filtered out
        expect(count($visibleSections))->toBe(1);
        expect($visibleSections[0]->getId())->toBe('filled');
    });

    test('deep nested data accessible', function (): void {
        $widget = ContextPanelWidget::make()
            ->fields([
                'Nested' => fn($data) => $data['level1']['level2']['value'],
            ])
            ->data([
                'level1' => [
                    'level2' => [
                        'value' => 'deeply nested',
                    ],
                ],
            ]);

        $lines = renderToLines($widget, 30, 10);
        $output = implode('', $lines);

        expect($output)->toContain('deeply nested');
    });
});

// =============================================================================
// DISPLAY OPTIONS TESTS
// =============================================================================

describe('display options', function (): void {
    test('showTitle can be toggled', function (): void {
        $widget = ContextPanelWidget::make()
            ->title('Test Title')
            ->showTitle(false);

        expect($widget->showsTitle())->toBeFalse();
    });

    test('showBorder can be toggled', function (): void {
        $widget = ContextPanelWidget::make()
            ->showBorder(false);

        expect($widget->showsBorder())->toBeFalse();
    });

    test('width can be set', function (): void {
        $widget = ContextPanelWidget::make()
            ->width(40);

        expect($widget->getWidth())->toBe(40);
    });

    test('height can be set', function (): void {
        $widget = ContextPanelWidget::make()
            ->height(20);

        expect($widget->getHeight())->toBe(20);
    });

    test('position can be set', function (): void {
        $widget = ContextPanelWidget::make()
            ->position('right');

        expect($widget->getPosition())->toBe('right');

        $widget->position('bottom');
        expect($widget->getPosition())->toBe('bottom');
    });

    test('emptyText can be customized', function (): void {
        $widget = ContextPanelWidget::make()
            ->emptyText('Select something to view details');

        expect($widget->getEmptyText())->toBe('Select something to view details');
    });

    test('scrollable can be toggled', function (): void {
        $widget = ContextPanelWidget::make()
            ->scrollable(false);

        expect($widget->isScrollable())->toBeFalse();

        $widget->scrollable(true);
        expect($widget->isScrollable())->toBeTrue();
    });
});

// =============================================================================
// CONTENT TESTS
// =============================================================================

describe('content', function (): void {
    test('static content can be set', function (): void {
        $widget = ContextPanelWidget::make()
            ->content('Static content here')
            ->data(['dummy' => true]);

        expect($widget->getContent())->toBe('Static content here');
    });

    test('contentFrom sets dynamic content callback', function (): void {
        $widget = ContextPanelWidget::make()
            ->contentFrom(fn($data) => "Hello, {$data['name']}!")
            ->data(['name' => 'World']);

        expect($widget->getContent())->toBe('Hello, World!');
    });

    test('getContent returns null when no content and no data', function (): void {
        $widget = ContextPanelWidget::make();

        expect($widget->getContent())->toBeNull();
    });
});

// =============================================================================
// SECTION CLASS TESTS
// =============================================================================

describe('section class', function (): void {
    test('section can have id', function (): void {
        $section = Section::make('test-id');

        expect($section->getId())->toBe('test-id');
    });

    test('section can have title', function (): void {
        $section = Section::make('test')
            ->title('Section Title');

        expect($section->getTitle())->toBe('Section Title');
    });

    test('section can have fields', function (): void {
        $section = Section::make('test')
            ->fields([
                'Key' => fn($d) => 'value',
            ]);

        expect($section->hasFields())->toBeTrue();
    });

    test('section can have component', function (): void {
        $section = Section::make('test')
            ->component(fn($d) => 'custom content');

        expect($section->hasComponent())->toBeTrue();
    });

    test('section can have visibility condition', function (): void {
        $section = Section::make('test')
            ->visible(fn($data) => false);

        expect($section->isVisible(['dummy' => true]))->toBeFalse();
    });

    test('section visibility defaults to true', function (): void {
        $section = Section::make('test')
            ->fields(['A' => fn($d) => 'a']);

        expect($section->isVisible(['dummy' => true]))->toBeTrue();
    });

    test('section can have boolean visibility', function (): void {
        $section = Section::make('test')
            ->visible(false);

        expect($section->isVisible(['dummy' => true]))->toBeFalse();
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = ContextPanelWidget::make();

        $result = $widget
            ->title('Test')
            ->width(40)
            ->height(20)
            ->showTitle(true)
            ->showBorder(true)
            ->position('right')
            ->emptyText('Empty')
            ->scrollable(true);

        expect($result)->toBeInstanceOf(ContextPanelWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = ContextPanelWidget::make()
            ->title(fn($data) => $data['name'] ?? 'Details')
            ->sections([
                Section::make('info')
                    ->title('Information')
                    ->fields([
                        'Status' => fn($d) => match ($d['status'] ?? 'unknown') {
                            'completed' => 'Completed',
                            'running' => 'Running',
                            'pending' => 'Pending',
                            default => 'Unknown',
                        },
                        'Source' => fn($d) => $d['source'] ?? '-',
                    ]),
                Section::make('timing')
                    ->title('Execution')
                    ->visible(fn($d) => ($d['status'] ?? '') === 'completed')
                    ->fields([
                        'Duration' => fn($d) => ($d['duration'] ?? 0) . 's',
                    ]),
            ])
            ->emptyText('Select a transform to view details')
            ->width(40)
            ->position('right');

        expect($widget->getEmptyText())->toBe('Select a transform to view details');
        expect($widget->getWidth())->toBe(40);
        expect($widget->getPosition())->toBe('right');
        expect(count($widget->getSections()))->toBe(2);
    });
});
