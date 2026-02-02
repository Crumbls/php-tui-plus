<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\ToastWidget;

/**
 * Toast Component Tests
 *
 * A temporary notification message that appears briefly and auto-dismisses.
 */

// =============================================================================
// DISPLAY TESTS
// =============================================================================

describe('display', function (): void {
    test('Toast renders with message', function (): void {
        $toast = ToastWidget::make()
            ->message('Transform saved successfully');

        $lines = renderToLines($toast, 50, 3);
        $output = implode('', $lines);

        expect($output)->toContain('Transform saved successfully');
    });

    test('Toast shows correct icon for success type', function (): void {
        $toast = ToastWidget::make()
            ->message('Saved!')
            ->type('success');

        expect($toast->getIcon())->toContain("\u{2713}"); // Checkmark
    });

    test('Toast shows correct icon for error type', function (): void {
        $toast = ToastWidget::make()
            ->message('Failed!')
            ->type('error');

        expect($toast->getIcon())->toContain("\u{2717}"); // X mark
    });

    test('Toast shows correct icon for warning type', function (): void {
        $toast = ToastWidget::make()
            ->message('Warning!')
            ->type('warning');

        expect($toast->getIcon())->toContain("\u{26A0}"); // Warning sign
    });

    test('Toast shows correct icon for info type', function (): void {
        $toast = ToastWidget::make()
            ->message('Info!')
            ->type('info');

        expect($toast->getIcon())->toContain("\u{2139}"); // Info symbol
    });

    test('Toast shows action button when provided', function (): void {
        $toast = ToastWidget::make()
            ->message('Transform deleted')
            ->action('Undo', fn() => null);

        expect($toast->hasAction())->toBeTrue();
    });

    test('Toast shows dismiss button when dismissable', function (): void {
        $toast = ToastWidget::make()
            ->message('Test message')
            ->dismissable(true);

        expect($toast->isDismissable())->toBeTrue();
    });

    test('Position top works correctly', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->position('top');

        expect($toast->getPosition())->toBe('top');
    });

    test('Position bottom works correctly', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->position('bottom');

        expect($toast->getPosition())->toBe('bottom');
    });
});

// =============================================================================
// TIMING TESTS
// =============================================================================

describe('timing', function (): void {
    test('Toast auto-dismisses after duration', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->duration(100); // 100ms

        // Simulate creation time in the past
        $reflection = new ReflectionClass($toast);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setAccessible(true);
        $createdAtProp->setValue($toast, (int) ((microtime(true) - 0.2) * 1000)); // 200ms ago

        expect($toast->isExpired())->toBeTrue();
    });

    test('duration: 0 prevents auto-dismiss', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->duration(0);

        // Even after simulating time, should not expire
        expect($toast->isExpired())->toBeFalse();
    });

    test('isExpired returns correct value', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->duration(3000);

        // Fresh toast should not be expired
        expect($toast->isExpired())->toBeFalse();
    });
});

// =============================================================================
// INTERACTION TESTS
// =============================================================================

describe('interaction', function (): void {
    test('dismiss() marks toast as dismissed', function (): void {
        $toast = ToastWidget::make()
            ->message('Test');

        expect($toast->isDismissed())->toBeFalse();

        $toast->dismiss();

        expect($toast->isDismissed())->toBeTrue();
    });

    test('Action callback executes on click', function (): void {
        $executed = false;

        $toast = ToastWidget::make()
            ->message('Deleted')
            ->action('Undo', function () use (&$executed): void {
                $executed = true;
            });

        $toast->executeAction();

        expect($executed)->toBeTrue();
    });

    test('Dismissable toast can be closed manually', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->dismissable(true);

        expect($toast->isDismissable())->toBeTrue();
        expect($toast->isDismissed())->toBeFalse();

        $toast->dismiss();

        expect($toast->isDismissed())->toBeTrue();
    });

    test('Non-dismissable toast setting works', function (): void {
        $toast = ToastWidget::make()
            ->message('Test')
            ->dismissable(false);

        expect($toast->isDismissable())->toBeFalse();
    });
});

// =============================================================================
// STATIC HELPERS TESTS
// =============================================================================

describe('static helpers', function (): void {
    test('success() creates success toast', function (): void {
        $toast = ToastWidget::success('Saved!');

        expect($toast->getMessage())->toBe('Saved!');
        expect($toast->getType())->toBe('success');
    });

    test('error() creates error toast', function (): void {
        $toast = ToastWidget::error('Failed!');

        expect($toast->getMessage())->toBe('Failed!');
        expect($toast->getType())->toBe('error');
    });

    test('warning() creates warning toast', function (): void {
        $toast = ToastWidget::warning('Caution!');

        expect($toast->getMessage())->toBe('Caution!');
        expect($toast->getType())->toBe('warning');
    });

    test('info() creates info toast', function (): void {
        $toast = ToastWidget::info('Note:');

        expect($toast->getMessage())->toBe('Note:');
        expect($toast->getType())->toBe('info');
    });

    test('success() with duration option', function (): void {
        $toast = ToastWidget::success('Saved!', duration: 2000);

        expect($toast->getDuration())->toBe(2000);
    });

    test('error() with dismissable option', function (): void {
        $toast = ToastWidget::error('Failed!', dismissable: true);

        expect($toast->isDismissable())->toBeTrue();
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('Very long message wraps or truncates', function (): void {
        $longMessage = str_repeat('a', 200);

        $toast = ToastWidget::make()
            ->message($longMessage);

        $lines = renderToLines($toast, 40, 5);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('Empty message renders correctly', function (): void {
        $toast = ToastWidget::make()
            ->message('');

        $lines = renderToLines($toast, 40, 3);
        // Should render without error
        expect(count($lines))->toBeGreaterThan(0);
    });

    test('Custom icon overrides default', function (): void {
        $toast = ToastWidget::make()
            ->message('Custom')
            ->type('success')
            ->icon('*');

        expect($toast->getIcon())->toBe('*');
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $toast = ToastWidget::make();

        $result = $toast
            ->message('Test')
            ->type('success')
            ->duration(3000)
            ->dismissable(true)
            ->position('top')
            ->icon('*');

        expect($result)->toBeInstanceOf(ToastWidget::class);
    });

    test('getId returns unique identifier', function (): void {
        $toast1 = ToastWidget::make()->message('Test 1');
        $toast2 = ToastWidget::make()->message('Test 2');

        expect($toast1->getId())->not->toBe($toast2->getId());
    });

    test('full configuration example works', function (): void {
        $toast = ToastWidget::make()
            ->message('Transform deleted')
            ->type('success')
            ->action('Undo', fn() => null)
            ->duration(5000)
            ->dismissable(true)
            ->position('top');

        expect($toast->getMessage())->toBe('Transform deleted');
        expect($toast->getType())->toBe('success');
        expect($toast->getDuration())->toBe(5000);
        expect($toast->hasAction())->toBeTrue();
        expect($toast->isDismissable())->toBeTrue();
        expect($toast->getPosition())->toBe('top');
    });
});

// =============================================================================
// DEFAULT VALUES TESTS
// =============================================================================

describe('default values', function (): void {
    test('default type is info', function (): void {
        $toast = ToastWidget::make()->message('Test');

        expect($toast->getType())->toBe('info');
    });

    test('default duration is 3000ms', function (): void {
        $toast = ToastWidget::make()->message('Test');

        expect($toast->getDuration())->toBe(3000);
    });

    test('default dismissable is true', function (): void {
        $toast = ToastWidget::make()->message('Test');

        expect($toast->isDismissable())->toBeTrue();
    });

    test('default position is top', function (): void {
        $toast = ToastWidget::make()->message('Test');

        expect($toast->getPosition())->toBe('top');
    });
});
