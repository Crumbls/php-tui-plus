<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\ToastManager;
use Crumbls\Tui\Extension\Core\Widget\ToastWidget;

/**
 * ToastManager Component Tests
 *
 * Manages multiple toast notifications with stacking and visibility control.
 */

// =============================================================================
// MANAGER TESTS
// =============================================================================

describe('manager', function (): void {
    test('add() adds toast to manager', function (): void {
        $manager = ToastManager::make();
        $toast = ToastWidget::success('Saved!');

        $id = $manager->add($toast);

        expect($id)->toBeString();
        expect($manager->hasToasts())->toBeTrue();
    });

    test('success() creates and adds success toast', function (): void {
        $manager = ToastManager::make();

        $id = $manager->success('Saved!');

        expect($id)->toBeString();
        expect($manager->hasToasts())->toBeTrue();

        $visible = $manager->getVisible();
        expect($visible[0]->getType())->toBe('success');
    });

    test('error() creates and adds error toast', function (): void {
        $manager = ToastManager::make();

        $id = $manager->error('Failed!');

        $visible = $manager->getVisible();
        expect($visible[0]->getType())->toBe('error');
    });

    test('warning() creates and adds warning toast', function (): void {
        $manager = ToastManager::make();

        $id = $manager->warning('Caution!');

        $visible = $manager->getVisible();
        expect($visible[0]->getType())->toBe('warning');
    });

    test('info() creates and adds info toast', function (): void {
        $manager = ToastManager::make();

        $id = $manager->info('Note:');

        $visible = $manager->getVisible();
        expect($visible[0]->getType())->toBe('info');
    });

    test('maxVisible limits displayed toasts', function (): void {
        $manager = ToastManager::make()
            ->maxVisible(2);

        $manager->success('Toast 1');
        $manager->success('Toast 2');
        $manager->success('Toast 3');
        $manager->success('Toast 4');

        $visible = $manager->getVisible();

        expect(count($visible))->toBe(2);
    });

    test('dismissAll() clears all toasts', function (): void {
        $manager = ToastManager::make();

        $manager->success('Toast 1');
        $manager->success('Toast 2');
        $manager->success('Toast 3');

        expect($manager->hasToasts())->toBeTrue();

        $manager->dismissAll();

        expect($manager->hasToasts())->toBeFalse();
    });

    test('dismiss() removes specific toast by ID', function (): void {
        $manager = ToastManager::make();

        $id1 = $manager->success('Toast 1');
        $id2 = $manager->success('Toast 2');

        $manager->dismiss($id1);

        $visible = $manager->getVisible();

        expect(count($visible))->toBe(1);
        expect($visible[0]->getId())->toBe($id2);
    });

    test('getVisible() returns only non-dismissed', function (): void {
        $manager = ToastManager::make();

        $id1 = $manager->success('Toast 1');
        $id2 = $manager->success('Toast 2');

        $manager->dismiss($id1);

        $visible = $manager->getVisible();

        expect(count($visible))->toBe(1);
        expect($visible[0]->getMessage())->toBe('Toast 2');
    });

    test('tick() removes expired toasts', function (): void {
        $manager = ToastManager::make();

        // Add a toast with very short duration
        $toast = ToastWidget::make()
            ->message('Quick toast')
            ->duration(1); // 1ms

        $manager->add($toast);

        // Simulate passage of time
        $reflection = new ReflectionClass($toast);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setAccessible(true);
        $createdAtProp->setValue($toast, (int) ((microtime(true) - 0.1) * 1000)); // 100ms ago

        $manager->tick();

        expect($manager->hasToasts())->toBeFalse();
    });
});

// =============================================================================
// CONFIGURATION TESTS
// =============================================================================

describe('configuration', function (): void {
    test('position() sets toast position', function (): void {
        $manager = ToastManager::make()
            ->position('bottom');

        expect($manager->getPosition())->toBe('bottom');
    });

    test('default position is top', function (): void {
        $manager = ToastManager::make();

        expect($manager->getPosition())->toBe('top');
    });

    test('default maxVisible is 3', function (): void {
        $manager = ToastManager::make();

        expect($manager->getMaxVisible())->toBe(3);
    });

    test('stackDirection sets stacking behavior', function (): void {
        $manager = ToastManager::make()
            ->stackDirection('up');

        expect($manager->getStackDirection())->toBe('up');
    });

    test('default stackDirection is down', function (): void {
        $manager = ToastManager::make();

        expect($manager->getStackDirection())->toBe('down');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('Manager handles empty state', function (): void {
        $manager = ToastManager::make();

        expect($manager->hasToasts())->toBeFalse();
        expect($manager->getVisible())->toBe([]);
    });

    test('Rapid additions do not break rendering', function (): void {
        $manager = ToastManager::make();

        for ($i = 0; $i < 50; $i++) {
            $manager->success("Toast {$i}");
        }

        $visible = $manager->getVisible();
        expect(count($visible))->toBe(3); // Default maxVisible
    });

    test('Dismiss non-existent toast does not error', function (): void {
        $manager = ToastManager::make();

        $manager->dismiss('non-existent-id');

        expect(true)->toBeTrue(); // No exception thrown
    });

    test('tick() on empty manager does not error', function (): void {
        $manager = ToastManager::make();

        $manager->tick();

        expect(true)->toBeTrue(); // No exception thrown
    });
});

// =============================================================================
// FLUENT INTERFACE TESTS
// =============================================================================

describe('fluent interface', function (): void {
    test('methods return self for chaining', function (): void {
        $manager = ToastManager::make();

        $result = $manager
            ->position('bottom')
            ->maxVisible(5)
            ->stackDirection('up');

        expect($result)->toBeInstanceOf(ToastManager::class);
    });

    test('full configuration example works', function (): void {
        $manager = ToastManager::make()
            ->position('top')
            ->maxVisible(3);

        $manager->success('Step 1 complete');
        $manager->success('Step 2 complete');

        expect($manager->getPosition())->toBe('top');
        expect($manager->getMaxVisible())->toBe(3);
        expect(count($manager->getVisible()))->toBe(2);
    });
});

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('manager renders as widget', function (): void {
        $manager = ToastManager::make();
        $manager->success('Test toast');

        $lines = renderToLines($manager, 60, 5);

        expect(count($lines))->toBeGreaterThan(0);
    });

    test('multiple toasts render stacked', function (): void {
        $manager = ToastManager::make();
        $manager->success('Toast 1');
        $manager->error('Toast 2');

        $lines = renderToLines($manager, 60, 10);
        $output = implode('', $lines);

        expect($output)->toContain('Toast 1');
        expect($output)->toContain('Toast 2');
    });
});
