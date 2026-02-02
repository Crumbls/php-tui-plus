<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Widget\Widget;

final class ToastManager implements Widget
{
    /** @var array<string, ToastWidget> */
    private array $toasts = [];

    private string $position = 'top';

    private int $maxVisible = 3;

    private string $stackDirection = 'down';

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    // =========================================================================
    // Configuration Methods
    // =========================================================================

    public function position(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function maxVisible(int $max): self
    {
        $this->maxVisible = $max;

        return $this;
    }

    public function stackDirection(string $direction): self
    {
        $this->stackDirection = $direction;

        return $this;
    }

    // =========================================================================
    // Toast Management Methods
    // =========================================================================

    public function add(ToastWidget $toast): string
    {
        $id = $toast->getId();
        $this->toasts[$id] = $toast;

        return $id;
    }

    public function success(string $message): string
    {
        $toast = ToastWidget::success($message);
        $this->toasts[$toast->getId()] = $toast;

        return $toast->getId();
    }

    public function error(string $message): string
    {
        $toast = ToastWidget::error($message);
        $this->toasts[$toast->getId()] = $toast;

        return $toast->getId();
    }

    public function warning(string $message): string
    {
        $toast = ToastWidget::warning($message);
        $this->toasts[$toast->getId()] = $toast;

        return $toast->getId();
    }

    public function info(string $message): string
    {
        $toast = ToastWidget::info($message);
        $this->toasts[$toast->getId()] = $toast;

        return $toast->getId();
    }

    public function dismiss(string $id): void
    {
        if (isset($this->toasts[$id])) {
            $this->toasts[$id]->dismiss();
            unset($this->toasts[$id]);
        }
    }

    public function dismissAll(): void
    {
        foreach ($this->toasts as $toast) {
            $toast->dismiss();
        }

        $this->toasts = [];
    }

    public function tick(): void
    {
        foreach ($this->toasts as $id => $toast) {
            if ($toast->isExpired() || $toast->isDismissed()) {
                unset($this->toasts[$id]);
            }
        }
    }

    // =========================================================================
    // Queries
    // =========================================================================

    /**
     * @return ToastWidget[]
     */
    public function getVisible(): array
    {
        $visible = [];

        foreach ($this->toasts as $toast) {
            if (!$toast->isDismissed() && !$toast->isExpired()) {
                $visible[] = $toast;
            }
        }

        return array_slice($visible, 0, $this->maxVisible);
    }

    public function hasToasts(): bool
    {
        foreach ($this->toasts as $toast) {
            if (!$toast->isDismissed() && !$toast->isExpired()) {
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // Getters
    // =========================================================================

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getMaxVisible(): int
    {
        return $this->maxVisible;
    }

    public function getStackDirection(): string
    {
        return $this->stackDirection;
    }
}
