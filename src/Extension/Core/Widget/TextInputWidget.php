<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class TextInputWidget implements Widget
{
    private string $value = '';

    private int $cursorPosition = 0;

    private bool $focused = false;

    private ?string $error = null;

    private ?string $label = null;

    private ?string $placeholder = null;

    private ?int $maxLength = null;

    private int $width = 30;

    private int $scrollOffset = 0;

    private int $labelWidth = 0;

    private bool $showLabel = true;

    private bool $inline = false;

    private bool $password = false;

    private bool $disabled = false;

    private bool $readonly = false;

    private bool $required = false;

    private ?string $pattern = null;

    private ?string $customErrorMessage = null;

    private ?Closure $validateFn = null;

    private ?Closure $onChange = null;

    private ?Closure $onSubmit = null;

    private ?Closure $onFocus = null;

    private ?Closure $onBlur = null;

    private ?Closure $onEscape = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'enter' => 'submit',
        'escape' => 'cancel',
        'left' => 'cursorLeft',
        'right' => 'cursorRight',
        'home' => 'cursorHome',
        'end' => 'cursorEnd',
        'backspace' => 'deleteBack',
        'delete' => 'deleteForward',
    ];

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;
        $this->clampCursor();

        return $this;
    }

    public function placeholder(string $text): self
    {
        $this->placeholder = $text;

        return $this;
    }

    public function maxLength(int $length): self
    {
        $this->maxLength = $length;

        return $this;
    }

    public function width(int $chars): self
    {
        $this->width = $chars;

        return $this;
    }

    public function labelWidth(int $chars): self
    {
        $this->labelWidth = $chars;

        return $this;
    }

    public function showLabel(bool $show = true): self
    {
        $this->showLabel = $show;

        return $this;
    }

    public function inline(bool $inline = false): self
    {
        $this->inline = $inline;

        return $this;
    }

    public function password(bool $password = false): self
    {
        $this->password = $password;

        return $this;
    }

    public function disabled(bool $disabled = false): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function readonly(bool $readonly = false): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function required(bool $required = false): self
    {
        $this->required = $required;

        return $this;
    }

    public function pattern(string $regex): self
    {
        $this->pattern = $regex;

        return $this;
    }

    public function errorMessage(string $message): self
    {
        $this->customErrorMessage = $message;

        return $this;
    }

    public function validateWith(Closure $fn): self
    {
        $this->validateFn = $fn;

        return $this;
    }

    public function onChange(Closure $fn): self
    {
        $this->onChange = $fn;

        return $this;
    }

    public function onSubmit(Closure $fn): self
    {
        $this->onSubmit = $fn;

        return $this;
    }

    public function onFocus(Closure $fn): self
    {
        $this->onFocus = $fn;

        return $this;
    }

    public function onBlur(Closure $fn): self
    {
        $this->onBlur = $fn;

        return $this;
    }

    public function onEscape(Closure $fn): self
    {
        $this->onEscape = $fn;

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

    public function focused(bool $focused): self
    {
        $this->focused = $focused;

        return $this;
    }

    public function cursorPosition(int $pos): self
    {
        $this->cursorPosition = $pos;
        $this->clampCursor();

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        $this->clampCursor();

        return $this;
    }

    public function clear(): self
    {
        $this->value = '';
        $this->cursorPosition = 0;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function cursorLeft(): self
    {
        if ($this->cursorPosition > 0) {
            $this->cursorPosition--;
        }

        return $this;
    }

    public function cursorRight(): self
    {
        if ($this->cursorPosition < mb_strlen($this->value)) {
            $this->cursorPosition++;
        }

        return $this;
    }

    public function cursorHome(): self
    {
        $this->cursorPosition = 0;

        return $this;
    }

    public function cursorEnd(): self
    {
        $this->cursorPosition = mb_strlen($this->value);

        return $this;
    }

    public function setCursorPosition(int $pos): self
    {
        $this->cursorPosition = $pos;
        $this->clampCursor();

        return $this;
    }

    public function getCursorPosition(): int
    {
        return $this->cursorPosition;
    }

    public function insert(string $text): self
    {
        $before = mb_substr($this->value, 0, $this->cursorPosition);
        $after = mb_substr($this->value, $this->cursorPosition);

        $newValue = $before . $text . $after;

        if ($this->maxLength !== null && mb_strlen($newValue) > $this->maxLength) {
            $availableSpace = $this->maxLength - mb_strlen($before) - mb_strlen($after);
            if ($availableSpace > 0) {
                $text = mb_substr($text, 0, $availableSpace);
                $newValue = $before . $text . $after;
            } else {
                return $this;
            }
        }

        $this->value = $newValue;
        $this->cursorPosition += mb_strlen($text);
        $this->clampCursor();

        if ($this->onChange !== null) {
            ($this->onChange)($this->value);
        }

        return $this;
    }

    public function deleteBack(): self
    {
        if ($this->cursorPosition > 0) {
            $before = mb_substr($this->value, 0, $this->cursorPosition - 1);
            $after = mb_substr($this->value, $this->cursorPosition);
            $this->value = $before . $after;
            $this->cursorPosition--;
        }

        return $this;
    }

    public function deleteForward(): self
    {
        $len = mb_strlen($this->value);
        if ($this->cursorPosition < $len) {
            $before = mb_substr($this->value, 0, $this->cursorPosition);
            $after = mb_substr($this->value, $this->cursorPosition + 1);
            $this->value = $before . $after;
        }

        return $this;
    }

    public function deleteWord(): self
    {
        if ($this->cursorPosition === 0) {
            return $this;
        }

        $before = mb_substr($this->value, 0, $this->cursorPosition);
        $after = mb_substr($this->value, $this->cursorPosition);

        $before = rtrim($before);
        $lastSpace = mb_strrpos($before, ' ');

        if ($lastSpace === false) {
            $before = '';
            $this->cursorPosition = 0;
        } else {
            $before = mb_substr($before, 0, $lastSpace + 1);
            $this->cursorPosition = mb_strlen($before);
        }

        $this->value = $before . $after;

        return $this;
    }

    public function clearLine(): self
    {
        $this->value = '';
        $this->cursorPosition = 0;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function validate(): self
    {
        $this->error = null;

        if ($this->required && $this->value === '') {
            $this->error = $this->customErrorMessage ?? 'This field is required';

            return $this;
        }

        if ($this->pattern !== null && $this->value !== '') {
            if (!preg_match($this->pattern, $this->value)) {
                $this->error = $this->customErrorMessage ?? 'Value does not match required pattern';

                return $this;
            }
        }

        if ($this->validateFn !== null) {
            $result = ($this->validateFn)($this->value);
            if ($result !== null) {
                $this->error = $result;

                return $this;
            }
        }

        return $this;
    }

    public function clearError(): self
    {
        $this->error = null;

        return $this;
    }

    public function submit(): void
    {
        if ($this->onSubmit !== null) {
            ($this->onSubmit)($this->value);
        }
    }

    public function cancel(): void
    {
        if ($this->onEscape !== null) {
            ($this->onEscape)();
        }
    }

    public function focus(): void
    {
        $this->focused = true;
        if ($this->onFocus !== null) {
            ($this->onFocus)();
        }
    }

    public function blur(): void
    {
        $this->focused = false;
        if ($this->onBlur !== null) {
            ($this->onBlur)($this->value);
        }
    }

    public function isFocused(): bool
    {
        return $this->focused;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLabelWidth(): int
    {
        return $this->labelWidth;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function isShowLabel(): bool
    {
        return $this->showLabel;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function isPassword(): bool
    {
        return $this->password;
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

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    private function clampCursor(): void
    {
        $len = mb_strlen($this->value);
        if ($this->cursorPosition < 0) {
            $this->cursorPosition = 0;
        } elseif ($this->cursorPosition > $len) {
            $this->cursorPosition = $len;
        }
    }
}
