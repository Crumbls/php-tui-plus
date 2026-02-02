<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class ToastWidget implements Widget
{
    private string $id;

    private string $message = '';

    private string $type = 'info';

    private int $duration = 3000;

    private bool $dismissable = true;

    private ?string $icon = null;

    /** @var array{label: string, callback: Closure}|null */
    private ?array $action = null;

    private int $createdAt;

    private bool $dismissed = false;

    private string $position = 'top';

    /** @var array<string, string> */
    private array $typeIcons = [
        'success' => "\u{2713}",
        'error' => "\u{2717}",
        'warning' => "\u{26A0}",
        'info' => "\u{2139}",
    ];

    private function __construct()
    {
        $this->id = uniqid('toast_', true);
        $this->createdAt = (int) (microtime(true) * 1000);
    }

    public static function make(): self
    {
        return new self();
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    public static function success(string $message, int $duration = 3000, bool $dismissable = true): self
    {
        return self::make()
            ->message($message)
            ->type('success')
            ->duration($duration)
            ->dismissable($dismissable);
    }

    public static function error(string $message, int $duration = 3000, bool $dismissable = true): self
    {
        return self::make()
            ->message($message)
            ->type('error')
            ->duration($duration)
            ->dismissable($dismissable);
    }

    public static function warning(string $message, int $duration = 3000, bool $dismissable = true): self
    {
        return self::make()
            ->message($message)
            ->type('warning')
            ->duration($duration)
            ->dismissable($dismissable);
    }

    public static function info(string $message, int $duration = 3000, bool $dismissable = true): self
    {
        return self::make()
            ->message($message)
            ->type('info')
            ->duration($duration)
            ->dismissable($dismissable);
    }

    // =========================================================================
    // Configuration Methods
    // =========================================================================

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function duration(int $ms): self
    {
        $this->duration = $ms;

        return $this;
    }

    public function dismissable(bool $dismissable = true): self
    {
        $this->dismissable = $dismissable;

        return $this;
    }

    public function position(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function action(string $label, Closure $callback): self
    {
        $this->action = [
            'label' => $label,
            'callback' => $callback,
        ];

        return $this;
    }

    // =========================================================================
    // Actions
    // =========================================================================

    public function dismiss(): void
    {
        $this->dismissed = true;
    }

    public function executeAction(): void
    {
        if ($this->action !== null) {
            ($this->action['callback'])();
        }
    }

    // =========================================================================
    // State Queries
    // =========================================================================

    public function isExpired(): bool
    {
        if ($this->duration === 0) {
            return false;
        }

        $now = (int) (microtime(true) * 1000);

        return ($now - $this->createdAt) >= $this->duration;
    }

    public function isDismissed(): bool
    {
        return $this->dismissed;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    // =========================================================================
    // Getters
    // =========================================================================

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function isDismissable(): bool
    {
        return $this->dismissable;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getIcon(): string
    {
        if ($this->icon !== null) {
            return $this->icon;
        }

        return $this->typeIcons[$this->type] ?? $this->typeIcons['info'];
    }

    /**
     * @return array{label: string, callback: Closure}|null
     */
    public function getAction(): ?array
    {
        return $this->action;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
}
