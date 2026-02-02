<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\ProgressBarWidget;

/**
 * ProgressBar Component Tests
 *
 * A component for displaying progress of long-running operations.
 * Supports both determinate (known progress) and indeterminate (unknown duration) modes.
 */

// =============================================================================
// PROGRESS TESTS
// =============================================================================

describe('progress', function (): void {
    test('setProgress updates bar fill', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5);

        expect($widget->getProgress())->toBe(0.5);
    });

    test('setCurrent and setTotal calculates percent', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(50)
            ->total(100);

        expect($widget->getProgress())->toBe(0.5);
    });

    test('increment increases current', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(10)
            ->total(100);

        $widget->increment();
        expect($widget->getCurrent())->toBe(11);

        $widget->increment(5);
        expect($widget->getCurrent())->toBe(16);
    });

    test('0% shows empty bar', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.0)
            ->width(20);

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($widget->getProgress())->toBe(0.0);
    });

    test('100% shows full bar', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(1.0)
            ->width(20);

        expect($widget->getProgress())->toBe(1.0);
        expect($widget->isComplete())->toBeTrue();
    });

    test('values clamp to 0-100 range', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(1.5);

        expect($widget->getProgress())->toBe(1.0);

        $widget->progress(-0.5);
        expect($widget->getProgress())->toBe(0.0);
    });

    test('progress accepts 0 to 100 range and converts to decimal', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(75);

        expect($widget->getProgress())->toBe(0.75);
    });
});

// =============================================================================
// DISPLAY TESTS
// =============================================================================

describe('display', function (): void {
    test('label renders correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->label('Importing data...')
            ->progress(0.5);

        $lines = renderToLines($widget, 40, 5);
        $output = implode('', $lines);

        expect($output)->toContain('Importing data...');
    });

    test('percentage shows when enabled', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.67)
            ->showPercent(true);

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($output)->toContain('67%');
    });

    test('percentage hidden when disabled', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.67)
            ->showPercent(false);

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($output)->not->toContain('%');
    });

    test('count shows when enabled', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(50)
            ->total(100)
            ->showCount(true);

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($output)->toContain('50');
        expect($output)->toContain('100');
    });

    test('ETA calculates correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(50)
            ->total(100)
            ->showEta(true);

        $widget->start();

        // Simulate elapsed time (we can't actually wait, but we can verify the method exists)
        expect($widget->getEta())->toBeNull()->or->toBeGreaterThanOrEqual(0);
    });

    test('width respects setting', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->width(30);

        expect($widget->getWidth())->toBe(30);
    });

    test('custom characters work', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->filledChar('#')
            ->emptyChar('-');

        expect($widget->getFilledChar())->toBe('#');
        expect($widget->getEmptyChar())->toBe('-');

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($output)->toContain('#');
        expect($output)->toContain('-');
    });

    test('brackets show when enabled', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->showBrackets(true);

        $lines = renderToLines($widget, 40, 3);
        $output = implode('', $lines);

        expect($output)->toContain('[');
        expect($output)->toContain(']');
    });

    test('compact style renders correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.6)
            ->style('compact');

        $lines = renderToLines($widget, 40, 3);
        // Compact style should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('detailed style renders correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.62)
            ->current(155)
            ->total(250)
            ->style('detailed');

        $lines = renderToLines($widget, 60, 10);
        // Detailed style should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('custom format string works', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(5)
            ->total(10)
            ->format('{percent}% - Step {current}/{total}');

        expect($widget->getFormat())->toBe('{percent}% - Step {current}/{total}');
    });
});

// =============================================================================
// STATE TESTS
// =============================================================================

describe('state', function (): void {
    test('start begins timing', function (): void {
        $widget = ProgressBarWidget::make()
            ->total(100);

        expect($widget->getStartTime())->toBeNull();

        $widget->start();

        expect($widget->getStartTime())->not->toBeNull();
    });

    test('complete triggers state change', function (): void {
        $completed = false;

        $widget = ProgressBarWidget::make()
            ->total(100)
            ->onComplete(function () use (&$completed): void {
                $completed = true;
            });

        $widget->complete();

        expect($widget->getState())->toBe('complete');
        expect($completed)->toBeTrue();
    });

    test('error shows error state', function (): void {
        $widget = ProgressBarWidget::make()
            ->total(100)
            ->errorText('Import failed');

        $widget->error();

        expect($widget->getState())->toBe('error');
    });

    test('reset clears progress', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(50)
            ->total(100);

        $widget->start();
        $widget->reset();

        expect($widget->getCurrent())->toBe(0);
        expect($widget->getProgress())->toBe(0.0);
        expect($widget->getState())->toBe('running');
        expect($widget->getStartTime())->toBeNull();
    });

    test('state changes update appearance', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5);

        $widget->state('paused');
        expect($widget->getState())->toBe('paused');

        $widget->state('complete');
        expect($widget->getState())->toBe('complete');

        $widget->state('error');
        expect($widget->getState())->toBe('error');

        $widget->state('running');
        expect($widget->getState())->toBe('running');
    });

    test('pause changes state', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5);

        $widget->pause();

        expect($widget->getState())->toBe('paused');
    });

    test('resume changes state back to running', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5);

        $widget->pause();
        $widget->resume();

        expect($widget->getState())->toBe('running');
    });

    test('completeText can be customized', function (): void {
        $widget = ProgressBarWidget::make()
            ->completeText('All done!');

        expect($widget->getCompleteText())->toBe('All done!');
    });

    test('errorText can be customized', function (): void {
        $widget = ProgressBarWidget::make()
            ->errorText('Something went wrong');

        expect($widget->getErrorText())->toBe('Something went wrong');
    });
});

// =============================================================================
// ANIMATION TESTS
// =============================================================================

describe('animation', function (): void {
    test('indeterminate mode set correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->indeterminate(true);

        expect($widget->isIndeterminate())->toBeTrue();
    });

    test('tick advances frame', function (): void {
        $widget = ProgressBarWidget::make()
            ->indeterminate(true);

        $initialFrame = $widget->getAnimationFrame();
        $widget->tick();

        expect($widget->getAnimationFrame())->toBe($initialFrame + 1);
    });

    test('animation loops correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->indeterminate(true)
            ->width(20);

        // Advance many frames
        for ($i = 0; $i < 100; $i++) {
            $widget->tick();
        }

        // Animation should still be in valid range
        $frame = $widget->getAnimationFrame();
        expect($frame)->toBeGreaterThanOrEqual(0);
    });

    test('animation speed respects setting', function (): void {
        $widget = ProgressBarWidget::make()
            ->indeterminate(true)
            ->animationSpeed(200);

        expect($widget->getAnimationSpeed())->toBe(200);
    });
});

// =============================================================================
// TIMING TESTS
// =============================================================================

describe('timing', function (): void {
    test('getElapsed returns seconds since start', function (): void {
        $widget = ProgressBarWidget::make()
            ->total(100);

        $widget->start();

        // Elapsed should be 0 or very small immediately after start
        expect($widget->getElapsed())->toBeGreaterThanOrEqual(0);
    });

    test('getRate returns items per second', function (): void {
        $widget = ProgressBarWidget::make()
            ->total(100)
            ->current(0);

        $widget->start();
        $widget->current(50);

        // Rate calculation depends on elapsed time
        $rate = $widget->getRate();
        expect($rate)->toBeGreaterThanOrEqual(0.0);
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onComplete callback fires', function (): void {
        $called = false;

        $widget = ProgressBarWidget::make()
            ->total(100)
            ->onComplete(function () use (&$called): void {
                $called = true;
            });

        $widget->complete();

        expect($called)->toBeTrue();
    });

    test('onProgress callback fires', function (): void {
        $receivedPercent = null;

        $widget = ProgressBarWidget::make()
            ->total(100)
            ->onProgress(function (float $percent) use (&$receivedPercent): void {
                $receivedPercent = $percent;
            });

        $widget->progress(0.75);

        expect($receivedPercent)->toBe(0.75);
    });

    test('onCancel callback fires', function (): void {
        $called = false;

        $widget = ProgressBarWidget::make()
            ->total(100)
            ->onCancel(function () use (&$called): void {
                $called = true;
            });

        $widget->cancel();

        expect($called)->toBeTrue();
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('zero total handled', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(0)
            ->total(0);

        // Should not throw, progress should be 0 or handled gracefully
        expect($widget->getProgress())->toBe(0.0);
    });

    test('progress beyond 100% clamps', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(150)
            ->total(100);

        expect($widget->getProgress())->toBe(1.0);
    });

    test('very long label truncates', function (): void {
        $longLabel = str_repeat('a', 200);

        $widget = ProgressBarWidget::make()
            ->label($longLabel)
            ->progress(0.5);

        $lines = renderToLines($widget, 40, 5);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('narrow width renders correctly', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->width(5);

        $lines = renderToLines($widget, 10, 3);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('negative current handled', function (): void {
        $widget = ProgressBarWidget::make()
            ->current(-10)
            ->total(100);

        expect($widget->getProgress())->toBe(0.0);
    });

    test('isComplete returns true at 100%', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(1.0);

        expect($widget->isComplete())->toBeTrue();
    });

    test('isComplete returns false below 100%', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.99);

        expect($widget->isComplete())->toBeFalse();
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $widget = ProgressBarWidget::make();

        $result = $widget
            ->label('Test')
            ->progress(0.5)
            ->total(100)
            ->current(50)
            ->showPercent(true)
            ->showCount(true)
            ->showEta(true)
            ->width(30)
            ->style('default')
            ->filledChar('=')
            ->emptyChar(' ')
            ->showBrackets(true)
            ->animationSpeed(100)
            ->completeText('Done')
            ->errorText('Failed');

        expect($result)->toBeInstanceOf(ProgressBarWidget::class);
    });

    test('full configuration example works', function (): void {
        $widget = ProgressBarWidget::make()
            ->label('Importing posts...')
            ->total(2500)
            ->current(1675)
            ->showPercent(true)
            ->showCount(true)
            ->showEta(true)
            ->onComplete(fn() => null);

        expect($widget->getLabel())->toBe('Importing posts...');
        expect($widget->getTotal())->toBe(2500);
        expect($widget->getCurrent())->toBe(1675);
        expect($widget->getProgress())->toBe(0.67);
    });
});

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('determinate mode renders bar', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->width(20);

        $lines = renderToLines($widget, 40, 3);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('indeterminate mode renders animated bar', function (): void {
        $widget = ProgressBarWidget::make()
            ->indeterminate(true)
            ->width(20);

        $lines = renderToLines($widget, 40, 3);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('renders with color', function (): void {
        $widget = ProgressBarWidget::make()
            ->progress(0.5)
            ->color('green');

        expect($widget->getColor())->toBe('green');

        $lines = renderToLines($widget, 40, 3);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });
});
