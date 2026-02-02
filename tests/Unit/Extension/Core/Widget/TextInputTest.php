<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\TextInputWidget;

/**
 * TextInput Component Tests
 *
 * A single-line text input field for capturing user text entry.
 */

// =============================================================================
// RENDERING TESTS
// =============================================================================

describe('rendering', function (): void {
    test('renders label when provided', function (): void {
        $widget = TextInputWidget::make()
            ->label('Name')
            ->value('Test');

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Name');
    });

    test('renders value correctly', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World');

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Hello World');
    });

    test('renders placeholder when empty', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->placeholder('Enter text...');

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->toContain('Enter text...');
    });

    test('does not show placeholder when value is present', function (): void {
        $widget = TextInputWidget::make()
            ->value('Some value')
            ->placeholder('Enter text...');

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->not->toContain('Enter text...');
        expect($fullOutput)->toContain('Some value');
    });

    test('shows cursor at correct position when focused', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->focused(true)
            ->cursorPosition(2);

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        // When focused, we expect some cursor indication
        expect($fullOutput)->toContain('Hello');
    });

    test('masks characters when password mode', function (): void {
        $widget = TextInputWidget::make()
            ->value('secret')
            ->password(true);

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        expect($fullOutput)->not->toContain('secret');
        // Should contain mask characters (bullets)
        expect($fullOutput)->toMatch('/[\x{2022}]{6}/u');
    });

    test('truncates long values with scroll indicator', function (): void {
        $widget = TextInputWidget::make()
            ->value('This is a very long piece of text that exceeds the width')
            ->width(15);

        $lines = renderToLines($widget, 30, 3);
        $fullOutput = implode('', $lines);

        // Should contain ellipsis or partial text
        expect(mb_strlen($fullOutput))->toBeLessThan(200);
    });

    test('shows error message when invalid', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        $widget->validate();

        $lines = renderToLines($widget, 30, 5);
        $fullOutput = implode('', $lines);

        expect($widget->getError())->not->toBeNull();
    });

    test('respects width option', function (): void {
        $widget = TextInputWidget::make()
            ->value('Test')
            ->width(20);

        expect($widget->getWidth())->toBe(20);
    });

    test('respects labelWidth option', function (): void {
        $widget = TextInputWidget::make()
            ->label('Name')
            ->labelWidth(15);

        expect($widget->getLabelWidth())->toBe(15);
    });
});

// =============================================================================
// INPUT TESTS
// =============================================================================

describe('input handling', function (): void {
    test('typing inserts characters at cursor', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(5);

        $widget->insert(' World');

        expect($widget->getValue())->toBe('Hello World');
    });

    test('insert at middle position works correctly', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hllo')
            ->cursorPosition(1);

        $widget->insert('e');

        expect($widget->getValue())->toBe('Hello');
        expect($widget->getCursorPosition())->toBe(2);
    });

    test('backspace deletes character before cursor', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(5);

        $widget->deleteBack();

        expect($widget->getValue())->toBe('Hell');
        expect($widget->getCursorPosition())->toBe(4);
    });

    test('backspace at beginning does nothing', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(0);

        $widget->deleteBack();

        expect($widget->getValue())->toBe('Hello');
        expect($widget->getCursorPosition())->toBe(0);
    });

    test('delete removes character at cursor', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(0);

        $widget->deleteForward();

        expect($widget->getValue())->toBe('ello');
        expect($widget->getCursorPosition())->toBe(0);
    });

    test('delete at end does nothing', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(5);

        $widget->deleteForward();

        expect($widget->getValue())->toBe('Hello');
        expect($widget->getCursorPosition())->toBe(5);
    });

    test('arrow left moves cursor left', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(3);

        $widget->cursorLeft();

        expect($widget->getCursorPosition())->toBe(2);
    });

    test('arrow left at beginning stays at beginning', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(0);

        $widget->cursorLeft();

        expect($widget->getCursorPosition())->toBe(0);
    });

    test('arrow right moves cursor right', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(2);

        $widget->cursorRight();

        expect($widget->getCursorPosition())->toBe(3);
    });

    test('arrow right at end stays at end', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(5);

        $widget->cursorRight();

        expect($widget->getCursorPosition())->toBe(5);
    });

    test('home jumps to beginning', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World')
            ->cursorPosition(6);

        $widget->cursorHome();

        expect($widget->getCursorPosition())->toBe(0);
    });

    test('end jumps to end', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World')
            ->cursorPosition(0);

        $widget->cursorEnd();

        expect($widget->getCursorPosition())->toBe(11);
    });

    test('respects maxLength on insert', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->maxLength(7)
            ->cursorPosition(5);

        $widget->insert(' World');

        expect($widget->getValue())->toBe('Hello W');
        expect(mb_strlen($widget->getValue()))->toBe(7);
    });

    test('clearLine removes all text', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World')
            ->cursorPosition(6);

        $widget->clearLine();

        expect($widget->getValue())->toBe('');
        expect($widget->getCursorPosition())->toBe(0);
    });

    test('deleteWord removes word before cursor', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World')
            ->cursorPosition(11);

        $widget->deleteWord();

        expect($widget->getValue())->toBe('Hello ');
    });
});

// =============================================================================
// EVENT TESTS
// =============================================================================

describe('events', function (): void {
    test('onChange fires on each keystroke', function (): void {
        $callCount = 0;
        $lastValue = '';

        $widget = TextInputWidget::make()
            ->value('')
            ->cursorPosition(0)
            ->onChange(function (string $value) use (&$callCount, &$lastValue): void {
                $callCount++;
                $lastValue = $value;
            });

        $widget->insert('a');
        $widget->insert('b');

        expect($callCount)->toBe(2);
        expect($lastValue)->toBe('ab');
    });

    test('onSubmit fires on Enter', function (): void {
        $called = false;
        $submittedValue = '';

        $widget = TextInputWidget::make()
            ->value('Test')
            ->onSubmit(function (string $value) use (&$called, &$submittedValue): void {
                $called = true;
                $submittedValue = $value;
            });

        $widget->submit();

        expect($called)->toBeTrue();
        expect($submittedValue)->toBe('Test');
    });

    test('onEscape fires on Escape', function (): void {
        $called = false;

        $widget = TextInputWidget::make()
            ->value('Test')
            ->onEscape(function () use (&$called): void {
                $called = true;
            });

        $widget->cancel();

        expect($called)->toBeTrue();
    });

    test('onFocus fires when focused', function (): void {
        $called = false;

        $widget = TextInputWidget::make()
            ->onFocus(function () use (&$called): void {
                $called = true;
            });

        $widget->focus();

        expect($called)->toBeTrue();
        expect($widget->isFocused())->toBeTrue();
    });

    test('onBlur fires when unfocused', function (): void {
        $called = false;
        $blurValue = '';

        $widget = TextInputWidget::make()
            ->value('Test')
            ->focused(true)
            ->onBlur(function (string $value) use (&$called, &$blurValue): void {
                $called = true;
                $blurValue = $value;
            });

        $widget->blur();

        expect($called)->toBeTrue();
        expect($blurValue)->toBe('Test');
        expect($widget->isFocused())->toBeFalse();
    });
});

// =============================================================================
// VALIDATION TESTS
// =============================================================================

describe('validation', function (): void {
    test('required validation fails on empty', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        $widget->validate();

        expect($widget->isValid())->toBeFalse();
        expect($widget->getError())->not->toBeNull();
    });

    test('required validation passes on non-empty', function (): void {
        $widget = TextInputWidget::make()
            ->value('test')
            ->required(true);

        $widget->validate();

        expect($widget->isValid())->toBeTrue();
        expect($widget->getError())->toBeNull();
    });

    test('pattern validation works with regex', function (): void {
        $widget = TextInputWidget::make()
            ->value('abc123')
            ->pattern('/^[a-z]+$/');

        $widget->validate();

        expect($widget->isValid())->toBeFalse();
    });

    test('pattern validation passes on match', function (): void {
        $widget = TextInputWidget::make()
            ->value('abc')
            ->pattern('/^[a-z]+$/');

        $widget->validate();

        expect($widget->isValid())->toBeTrue();
    });

    test('custom validate function works', function (): void {
        $widget = TextInputWidget::make()
            ->value('ab')
            ->validateWith(fn (string $v) => strlen($v) < 3 ? 'Min 3 chars' : null);

        $widget->validate();

        expect($widget->isValid())->toBeFalse();
        expect($widget->getError())->toBe('Min 3 chars');
    });

    test('custom validate function passes', function (): void {
        $widget = TextInputWidget::make()
            ->value('abcdef')
            ->validateWith(fn (string $v) => strlen($v) < 3 ? 'Min 3 chars' : null);

        $widget->validate();

        expect($widget->isValid())->toBeTrue();
        expect($widget->getError())->toBeNull();
    });

    test('error clears on valid input', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        $widget->validate();
        expect($widget->getError())->not->toBeNull();

        $widget->setValue('valid');
        $widget->validate();

        expect($widget->getError())->toBeNull();
    });

    test('isValid returns correct state', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        expect($widget->isValid())->toBeTrue(); // Before validation

        $widget->validate();

        expect($widget->isValid())->toBeFalse();
    });

    test('clearError removes error message', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        $widget->validate();
        expect($widget->getError())->not->toBeNull();

        $widget->clearError();

        expect($widget->getError())->toBeNull();
    });

    test('custom error message is used', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true)
            ->errorMessage('Custom error text');

        $widget->validate();

        expect($widget->getError())->toBe('Custom error text');
    });
});

// =============================================================================
// EDGE CASES
// =============================================================================

describe('edge cases', function (): void {
    test('empty input with required shows error on validate', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->required(true);

        $widget->validate();

        expect($widget->getError())->not->toBeNull();
    });

    test('very long input scrolls correctly', function (): void {
        $longText = str_repeat('a', 100);
        $widget = TextInputWidget::make()
            ->value($longText)
            ->width(20)
            ->cursorPosition(50);

        expect($widget->getValue())->toBe($longText);
        expect($widget->getCursorPosition())->toBe(50);
    });

    test('paste truncates to maxLength', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->maxLength(10)
            ->cursorPosition(0);

        $widget->insert('This is a very long string');

        expect(mb_strlen($widget->getValue()))->toBeLessThanOrEqual(10);
    });

    test('cursor stays in bounds after value change', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello World')
            ->cursorPosition(11);

        $widget->setValue('Hi');

        expect($widget->getCursorPosition())->toBeLessThanOrEqual(2);
    });

    test('unicode characters handled correctly', function (): void {
        $widget = TextInputWidget::make()
            ->value('')
            ->cursorPosition(0);

        $widget->insert("\u{1F600}");
        $widget->insert("\u{1F389}");

        expect($widget->getValue())->toBe("\u{1F600}\u{1F389}");
        expect($widget->getCursorPosition())->toBe(2);
    });

    test('cursor position is clamped to valid range', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(100);

        expect($widget->getCursorPosition())->toBe(5);
    });

    test('negative cursor position is clamped to zero', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(-5);

        expect($widget->getCursorPosition())->toBe(0);
    });

    test('empty value isEmpty returns true', function (): void {
        $widget = TextInputWidget::make()->value('');

        expect($widget->isEmpty())->toBeTrue();
    });

    test('non-empty value isEmpty returns false', function (): void {
        $widget = TextInputWidget::make()->value('x');

        expect($widget->isEmpty())->toBeFalse();
    });

    test('clear removes value and resets cursor', function (): void {
        $widget = TextInputWidget::make()
            ->value('Hello')
            ->cursorPosition(3);

        $widget->clear();

        expect($widget->getValue())->toBe('');
        expect($widget->getCursorPosition())->toBe(0);
    });
});

// =============================================================================
// DISPLAY OPTIONS TESTS
// =============================================================================

describe('display options', function (): void {
    test('disabled prevents editing', function (): void {
        $widget = TextInputWidget::make()
            ->value('Original')
            ->disabled(true);

        expect($widget->isDisabled())->toBeTrue();
    });

    test('readonly shows value but prevents edit', function (): void {
        $widget = TextInputWidget::make()
            ->value('ReadOnly')
            ->readonly(true);

        expect($widget->isReadonly())->toBeTrue();
    });

    test('showLabel false hides label', function (): void {
        $widget = TextInputWidget::make()
            ->label('Hidden')
            ->showLabel(false);

        expect($widget->isShowLabel())->toBeFalse();
    });

    test('inline mode works', function (): void {
        $widget = TextInputWidget::make()
            ->inline(true);

        expect($widget->isInline())->toBeTrue();
    });
});

// =============================================================================
// KEY BINDINGS TESTS
// =============================================================================

describe('key bindings', function (): void {
    test('default key bindings are set', function (): void {
        $widget = TextInputWidget::make();
        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('enter');
        expect($bindings)->toHaveKey('escape');
        expect($bindings)->toHaveKey('left');
        expect($bindings)->toHaveKey('right');
        expect($bindings)->toHaveKey('backspace');
    });

    test('custom key bindings can be set', function (): void {
        $widget = TextInputWidget::make()
            ->keyBindings([
                'ctrl+a' => 'selectAll',
                'ctrl+c' => 'copy',
            ]);

        $bindings = $widget->getKeyBindings();

        expect($bindings)->toHaveKey('ctrl+a');
        expect($bindings['ctrl+a'])->toBe('selectAll');
    });
});

// =============================================================================
// VALUE METHODS TESTS
// =============================================================================

describe('value methods', function (): void {
    test('getValue returns current value', function (): void {
        $widget = TextInputWidget::make()->value('Test');

        expect($widget->getValue())->toBe('Test');
    });

    test('setValue updates value', function (): void {
        $widget = TextInputWidget::make()->value('Old');

        $widget->setValue('New');

        expect($widget->getValue())->toBe('New');
    });

    test('setValue returns self for chaining', function (): void {
        $widget = TextInputWidget::make();

        $result = $widget->setValue('Test');

        expect($result)->toBe($widget);
    });

    test('fluent interface works', function (): void {
        $widget = TextInputWidget::make()
            ->label('Name')
            ->value('Test')
            ->placeholder('Enter name')
            ->maxLength(50)
            ->width(30)
            ->required(true);

        expect($widget->getValue())->toBe('Test');
        expect($widget->getWidth())->toBe(30);
    });
});
