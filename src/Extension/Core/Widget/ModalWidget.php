<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Extension\Core\Widget\Modal\Button;
use Crumbls\Tui\Widget\Widget;

final class ModalWidget implements Widget
{
    private ?string $title = null;

    private string|Widget|null $body = null;

    private string|Widget|null $footer = null;

    /** @var array<int, Button> */
    private array $buttons = [];

    private int $focusedButton = 0;

    private bool $isOpen = false;

    private ?int $width = null;

    private ?int $height = null;

    private ?int $minWidth = null;

    private ?int $minHeight = null;

    private ?int $maxWidth = null;

    private ?int $maxHeight = null;

    private bool $fitContent = false;

    private bool $centered = true;

    private ?int $positionX = null;

    private ?int $positionY = null;

    private bool $overlay = true;

    private string $overlayStyle = 'dim';

    private bool $closeOnEscape = true;

    private bool $closeOnClickOutside = false;

    private bool $trapFocus = true;

    private ?string $focusFirst = null;

    private bool $hasInput = false;

    private ?Closure $onConfirm = null;

    private ?Closure $onCancel = null;

    private ?Closure $onClose = null;

    private ?Closure $onOpen = null;

    private ?Closure $onButtonClick = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'enter' => 'clickFocusedButton',
        'escape' => 'cancel',
        'tab' => 'focusNext',
        'shift+tab' => 'focusPrevious',
        'left' => 'focusPreviousButton',
        'right' => 'focusNextButton',
    ];

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    public static function confirmation(string $message, Closure $onConfirm): self
    {
        return self::make()
            ->title($message)
            ->buttons('yes-no')
            ->onConfirm($onConfirm)
            ->open();
    }

    public static function alert(string $message): self
    {
        return self::make()
            ->title($message)
            ->buttons('ok')
            ->open();
    }

    public static function error(string $title, string $message): self
    {
        return self::make()
            ->title($title)
            ->body($message)
            ->buttons('ok')
            ->open();
    }

    public static function prompt(string $title, Closure $onSubmit): self
    {
        $widget = self::make()
            ->title($title)
            ->buttons('ok-cancel')
            ->onConfirm($onSubmit)
            ->open();

        $widget->hasInput = true;

        return $widget;
    }

    // =========================================================================
    // Content Methods
    // =========================================================================

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function body(string|Widget $content): self
    {
        $this->body = $content;

        return $this;
    }

    public function footer(string|Widget $content): self
    {
        $this->footer = $content;

        return $this;
    }

    // =========================================================================
    // Size Methods
    // =========================================================================

    public function width(int $chars): self
    {
        $this->width = $chars;

        return $this;
    }

    public function height(int $rows): self
    {
        $this->height = $rows;

        return $this;
    }

    public function minWidth(int $chars): self
    {
        $this->minWidth = $chars;

        return $this;
    }

    public function minHeight(int $rows): self
    {
        $this->minHeight = $rows;

        return $this;
    }

    public function maxWidth(int $chars): self
    {
        $this->maxWidth = $chars;

        return $this;
    }

    public function maxHeight(int $rows): self
    {
        $this->maxHeight = $rows;

        return $this;
    }

    public function fitContent(bool $fit = true): self
    {
        $this->fitContent = $fit;

        return $this;
    }

    // =========================================================================
    // Button Methods
    // =========================================================================

    /**
     * @param string|array<int, Button> $buttons
     */
    public function buttons(string|array $buttons): self
    {
        if (is_string($buttons)) {
            $this->buttons = $this->createPresetButtons($buttons);
        } else {
            $this->buttons = $buttons;
        }

        return $this;
    }

    public function confirmButton(string $label = 'OK', string $style = 'primary'): self
    {
        foreach ($this->buttons as $button) {
            if (in_array($button->getId(), ['ok', 'yes'], true)) {
                $button->label($label)->style($style);
                break;
            }
        }

        return $this;
    }

    public function cancelButton(string $label = 'Cancel', string $style = 'secondary'): self
    {
        foreach ($this->buttons as $button) {
            if (in_array($button->getId(), ['cancel', 'no'], true)) {
                $button->label($label)->style($style);
                break;
            }
        }

        return $this;
    }

    // =========================================================================
    // Behavior Methods
    // =========================================================================

    public function closeOnEscape(bool $close = true): self
    {
        $this->closeOnEscape = $close;

        return $this;
    }

    public function closeOnClickOutside(bool $close = false): self
    {
        $this->closeOnClickOutside = $close;

        return $this;
    }

    public function trapFocus(bool $trap = true): self
    {
        $this->trapFocus = $trap;

        return $this;
    }

    public function focusFirst(string $componentId): self
    {
        $this->focusFirst = $componentId;

        return $this;
    }

    // =========================================================================
    // Display Methods
    // =========================================================================

    public function centered(bool $centered = true): self
    {
        $this->centered = $centered;

        return $this;
    }

    public function position(int $x, int $y): self
    {
        $this->positionX = $x;
        $this->positionY = $y;
        $this->centered = false;

        return $this;
    }

    public function overlay(bool $show = true): self
    {
        $this->overlay = $show;

        return $this;
    }

    public function overlayStyle(string $style): self
    {
        $this->overlayStyle = $style;

        return $this;
    }

    // =========================================================================
    // Event Methods
    // =========================================================================

    public function onConfirm(Closure $fn): self
    {
        $this->onConfirm = $fn;

        return $this;
    }

    public function onCancel(Closure $fn): self
    {
        $this->onCancel = $fn;

        return $this;
    }

    public function onClose(Closure $fn): self
    {
        $this->onClose = $fn;

        return $this;
    }

    public function onOpen(Closure $fn): self
    {
        $this->onOpen = $fn;

        return $this;
    }

    public function onButtonClick(Closure $fn): self
    {
        $this->onButtonClick = $fn;

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

    // =========================================================================
    // Control Methods
    // =========================================================================

    public function open(): self
    {
        if (!$this->isOpen) {
            $this->isOpen = true;
            if ($this->onOpen !== null) {
                ($this->onOpen)();
            }
        }

        return $this;
    }

    public function close(?string $result = null): self
    {
        if ($this->isOpen) {
            $this->isOpen = false;
            if ($this->onClose !== null && $result !== null) {
                ($this->onClose)($result);
            }
        }

        return $this;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function confirm(): void
    {
        if ($this->onConfirm !== null) {
            ($this->onConfirm)();
        }
        $this->close('confirm');
    }

    public function cancel(): void
    {
        if ($this->onCancel !== null) {
            ($this->onCancel)();
        }
        $this->close('cancel');
    }

    // =========================================================================
    // Button Navigation Methods
    // =========================================================================

    /**
     * @return array<int, Button>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function focusNextButton(): self
    {
        if (count($this->buttons) === 0) {
            return $this;
        }

        $this->focusedButton++;
        if ($this->focusedButton >= count($this->buttons)) {
            $this->focusedButton = 0;
        }

        return $this;
    }

    public function focusPreviousButton(): self
    {
        if (count($this->buttons) === 0) {
            return $this;
        }

        $this->focusedButton--;
        if ($this->focusedButton < 0) {
            $this->focusedButton = count($this->buttons) - 1;
        }

        return $this;
    }

    public function clickFocusedButton(): void
    {
        if (count($this->buttons) === 0) {
            return;
        }

        $button = $this->buttons[$this->focusedButton] ?? null;
        if ($button !== null) {
            $this->clickButton($button->getId());
        }
    }

    public function clickButton(string $id): void
    {
        if ($this->onButtonClick !== null) {
            ($this->onButtonClick)($id);
        }

        if ($id === 'ok' || $id === 'yes') {
            $this->confirm();
        } elseif ($id === 'cancel' || $id === 'no') {
            $this->cancel();
        }
    }

    public function getFocusedButtonIndex(): int
    {
        return $this->focusedButton;
    }

    // =========================================================================
    // Content Getter/Setter Methods
    // =========================================================================

    public function getBody(): string|Widget|null
    {
        return $this->body;
    }

    public function setBody(string|Widget $content): self
    {
        $this->body = $content;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFooter(): string|Widget|null
    {
        return $this->footer;
    }

    // =========================================================================
    // Getter Methods
    // =========================================================================

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getMinWidth(): ?int
    {
        return $this->minWidth;
    }

    public function getMinHeight(): ?int
    {
        return $this->minHeight;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function isFitContent(): bool
    {
        return $this->fitContent;
    }

    public function isCentered(): bool
    {
        return $this->centered;
    }

    public function getPositionX(): ?int
    {
        return $this->positionX;
    }

    public function getPositionY(): ?int
    {
        return $this->positionY;
    }

    public function hasOverlay(): bool
    {
        return $this->overlay;
    }

    public function getOverlayStyle(): string
    {
        return $this->overlayStyle;
    }

    public function closesOnEscape(): bool
    {
        return $this->closeOnEscape;
    }

    public function closesOnClickOutside(): bool
    {
        return $this->closeOnClickOutside;
    }

    public function isFocusTrapped(): bool
    {
        return $this->trapFocus;
    }

    public function getFocusFirst(): ?string
    {
        return $this->focusFirst;
    }

    public function hasInput(): bool
    {
        return $this->hasInput;
    }

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    // =========================================================================
    // Private Methods
    // =========================================================================

    /**
     * @return array<int, Button>
     */
    private function createPresetButtons(string $preset): array
    {
        return match ($preset) {
            'ok' => [
                Button::make('ok')->label('OK')->style('primary'),
            ],
            'ok-cancel' => [
                Button::make('cancel')->label('Cancel')->style('secondary'),
                Button::make('ok')->label('OK')->style('primary'),
            ],
            'yes-no' => [
                Button::make('no')->label('No')->style('secondary'),
                Button::make('yes')->label('Yes')->style('primary'),
            ],
            'yes-no-cancel' => [
                Button::make('cancel')->label('Cancel')->style('secondary'),
                Button::make('no')->label('No')->style('secondary'),
                Button::make('yes')->label('Yes')->style('primary'),
            ],
            default => [],
        };
    }
}
