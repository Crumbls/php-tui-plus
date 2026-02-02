<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\StatusBarWidget;

/**
 * StatusBar Component Tests
 *
 * A fixed bar that displays available actions, key hints, and contextual information.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders hints correctly', function (): void {
        $widget = StatusBarWidget::make()
            ->hints([
                'enter' => 'open',
                'n' => 'new',
                'd' => 'delete',
            ]);

        $lines = renderToLines($widget, 60, 1);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('[');
        expect($fullOutput)->toContain('open');
        expect($fullOutput)->toContain('new');
        expect($fullOutput)->toContain('delete');
    });

    test('renders sections (left/center/right)', function (): void {
        $widget = StatusBarWidget::make()
            ->left('Left Content')
            ->center('Center Content')
            ->right('Right Content');

        $lines = renderToLines($widget, 60, 1);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Left Content');
        expect($fullOutput)->toContain('Center Content');
        expect($fullOutput)->toContain('Right Content');
    });

    test('formats key names correctly', function (): void {
        $widget = StatusBarWidget::make()
            ->hints([
                'enter' => 'confirm',
                'escape' => 'cancel',
                'space' => 'toggle',
            ]);

        $formattedHints = $widget->getFormattedHints();

        expect($formattedHints)->toContain(['key' => "\u{21B5}", 'action' => 'confirm']);
        expect($formattedHints)->toContain(['key' => 'esc', 'action' => 'cancel']);
        expect($formattedHints)->toContain(['key' => "\u{2423}", 'action' => 'toggle']);
    });

    test('applies separator between hints', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['a' => 'one', 'b' => 'two'])
            ->separator(' | ');

        expect($widget->getSeparator())->toBe(' | ');
    });

    test('shows border when enabled', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['q' => 'quit'])
            ->showBorder(true);

        expect($widget->hasBorder())->toBeTrue();
    });

    test('respects position (top/bottom)', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['q' => 'quit'])
            ->position('top');

        expect($widget->getPosition())->toBe('top');

        $widget->position('bottom');
        expect($widget->getPosition())->toBe('bottom');
    });
});

// =============================================================================
// CONTENT TESTS
// =============================================================================

describe('content', function (): void {
    test('setHints replaces all hints', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['a' => 'one', 'b' => 'two']);

        $widget->setHints(['c' => 'three']);

        expect($widget->getHints())->toBe(['c' => 'three']);
    });

    test('addHint adds single hint', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['a' => 'one']);

        $widget->addHint('b', 'two');

        expect($widget->getHints())->toBe(['a' => 'one', 'b' => 'two']);
    });

    test('removeHint removes hint', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['a' => 'one', 'b' => 'two', 'c' => 'three']);

        $widget->removeHint('b');

        expect($widget->getHints())->toBe(['a' => 'one', 'c' => 'three']);
    });

    test('clearHints removes all hints', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['a' => 'one', 'b' => 'two']);

        $widget->clearHints();

        expect($widget->getHints())->toBe([]);
    });

    test('sections render in correct positions', function (): void {
        $widget = StatusBarWidget::make()
            ->left('LEFT')
            ->center('CENTER')
            ->right('RIGHT');

        expect($widget->getLeft())->toBe('LEFT');
        expect($widget->getCenter())->toBe('CENTER');
        expect($widget->getRight())->toBe('RIGHT');
    });

    test('long content truncates gracefully', function (): void {
        $longText = str_repeat('x', 200);
        $widget = StatusBarWidget::make()
            ->left($longText);

        $lines = renderToLines($widget, 40, 1);
        $fullOutput = implode('', $lines);

        expect(mb_strlen($fullOutput))->toBeLessThanOrEqual(40);
    });
});

// =============================================================================
// CONTEXT TESTS
// =============================================================================

describe('context', function (): void {
    test('setContext switches hint set', function (): void {
        $widget = StatusBarWidget::make()
            ->contexts([
                'list' => ['enter' => 'open', 'n' => 'new'],
                'edit' => ['enter' => 'save', 'escape' => 'cancel'],
            ])
            ->context('list');

        expect($widget->getContext())->toBe('list');
        expect($widget->getHints())->toBe(['enter' => 'open', 'n' => 'new']);

        $widget->setContext('edit');

        expect($widget->getContext())->toBe('edit');
        expect($widget->getHints())->toBe(['enter' => 'save', 'escape' => 'cancel']);
    });

    test('invalid context falls back gracefully', function (): void {
        $widget = StatusBarWidget::make()
            ->contexts([
                'list' => ['enter' => 'open'],
            ])
            ->context('list');

        $widget->setContext('nonexistent');

        // Should keep previous context or have empty hints
        expect($widget->getHints())->toBeArray();
    });

    test('addContext registers new context', function (): void {
        $widget = StatusBarWidget::make()
            ->contexts([
                'list' => ['enter' => 'open'],
            ]);

        $widget->addContext('confirm', ['y' => 'yes', 'n' => 'no']);

        expect($widget->hasContext('confirm'))->toBeTrue();
    });

    test('context changes update display', function (): void {
        $widget = StatusBarWidget::make()
            ->contexts([
                'list' => ['q' => 'quit'],
                'edit' => ['s' => 'save'],
            ])
            ->context('list');

        $lines1 = renderToLines($widget, 60, 1);
        $output1 = implode('', $lines1);
        expect($output1)->toContain('quit');

        $widget->setContext('edit');

        $lines2 = renderToLines($widget, 60, 1);
        $output2 = implode('', $lines2);
        expect($output2)->toContain('save');
    });
});

// =============================================================================
// BREADCRUMB TESTS
// =============================================================================

describe('breadcrumbs', function (): void {
    test('breadcrumbs render with separator', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs(['Extracts', 'wordpress-export', 'Posts'])
            ->breadcrumbSeparator(' > ');

        $lines = renderToLines($widget, 60, 1);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Extracts');
        expect($fullOutput)->toContain('>');
        expect($fullOutput)->toContain('wordpress-export');
        expect($fullOutput)->toContain('Posts');
    });

    test('pushBreadcrumb adds item', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs(['Home', 'Projects']);

        $widget->pushBreadcrumb('Details');

        expect($widget->getBreadcrumbs())->toBe(['Home', 'Projects', 'Details']);
    });

    test('popBreadcrumb removes last item', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs(['Home', 'Projects', 'Details']);

        $widget->popBreadcrumb();

        expect($widget->getBreadcrumbs())->toBe(['Home', 'Projects']);
    });

    test('empty breadcrumbs render nothing', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs([]);

        expect($widget->getBreadcrumbs())->toBe([]);
    });

    test('setBreadcrumbs replaces all', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs(['Old1', 'Old2']);

        $widget->setBreadcrumbs(['New1', 'New2', 'New3']);

        expect($widget->getBreadcrumbs())->toBe(['New1', 'New2', 'New3']);
    });

    test('default breadcrumb separator is correct', function (): void {
        $widget = StatusBarWidget::make()
            ->breadcrumbs(['A', 'B']);

        expect($widget->getBreadcrumbSeparator())->toBe(' > ');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty hints renders empty bar', function (): void {
        $widget = StatusBarWidget::make()
            ->hints([]);

        expect($widget->getHints())->toBe([]);

        $lines = renderToLines($widget, 40, 1);
        $fullOutput = implode('', $lines);
        expect($fullOutput)->toBeString();
    });

    test('very long hints truncate', function (): void {
        $longAction = str_repeat('a', 100);
        $widget = StatusBarWidget::make()
            ->hints(['x' => $longAction]);

        $lines = renderToLines($widget, 40, 1);
        $fullOutput = implode('', $lines);

        expect(mb_strlen($fullOutput))->toBeLessThanOrEqual(40);
    });

    test('many hints handle correctly', function (): void {
        $manyHints = [];
        for ($i = 0; $i < 20; $i++) {
            $manyHints["k{$i}"] = "action{$i}";
        }

        $widget = StatusBarWidget::make()
            ->hints($manyHints);

        expect(count($widget->getHints()))->toBe(20);
    });

    test('special characters in hints escape properly', function (): void {
        $widget = StatusBarWidget::make()
            ->hints(['<' => 'less', '>' => 'greater', '&' => 'ampersand']);

        expect($widget->getHints())->toHaveKey('<');
        expect($widget->getHints())->toHaveKey('>');
        expect($widget->getHints())->toHaveKey('&');
    });
});

// =============================================================================
// KEY FORMATTING TESTS
// =============================================================================

describe('key formatting', function (): void {
    test('enter key formats to arrow symbol', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('enter'))->toBe("\u{21B5}");
    });

    test('escape key formats to esc', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('escape'))->toBe('esc');
    });

    test('space key formats to space symbol', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('space'))->toBe("\u{2423}");
    });

    test('arrow keys format to symbols', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('up'))->toBe("\u{2191}");
        expect($widget->formatKey('down'))->toBe("\u{2193}");
        expect($widget->formatKey('left'))->toBe("\u{2190}");
        expect($widget->formatKey('right'))->toBe("\u{2192}");
    });

    test('tab key formats to tab symbol', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('tab'))->toBe("\u{21E5}");
    });

    test('ctrl combinations format correctly', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('ctrl+s'))->toBe('^S');
        expect($widget->formatKey('ctrl+x'))->toBe('^X');
    });

    test('regular keys pass through unchanged', function (): void {
        $widget = StatusBarWidget::make();
        expect($widget->formatKey('q'))->toBe('q');
        expect($widget->formatKey('n'))->toBe('n');
        expect($widget->formatKey('/'))->toBe('/');
    });
});

// =============================================================================
// CONFIGURATION TESTS
// =============================================================================

describe('configuration', function (): void {
    test('padding can be set', function (): void {
        $widget = StatusBarWidget::make()
            ->padding(2);

        expect($widget->getPadding())->toBe(2);
    });

    test('style can be set', function (): void {
        $widget = StatusBarWidget::make()
            ->style('minimal');

        expect($widget->getStyle())->toBe('minimal');
    });

    test('default values are correct', function (): void {
        $widget = StatusBarWidget::make();

        expect($widget->getPosition())->toBe('bottom');
        expect($widget->hasBorder())->toBeFalse();
        expect($widget->getSeparator())->toBe('  ');
        expect($widget->getPadding())->toBe(1);
        expect($widget->getStyle())->toBe('default');
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = StatusBarWidget::make();

        $result = $widget
            ->hints(['q' => 'quit'])
            ->position('bottom')
            ->showBorder(true)
            ->padding(2)
            ->separator(' | ');

        expect($result)->toBeInstanceOf(StatusBarWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = StatusBarWidget::make()
            ->left('Extracts > wordpress')
            ->center('[n]ew [d]elete')
            ->right('3 items')
            ->position('bottom')
            ->showBorder(true)
            ->padding(1);

        expect($widget->getLeft())->toBe('Extracts > wordpress');
        expect($widget->getCenter())->toBe('[n]ew [d]elete');
        expect($widget->getRight())->toBe('3 items');
        expect($widget->getPosition())->toBe('bottom');
        expect($widget->hasBorder())->toBeTrue();
    });
});
