<?php

declare(strict_types=1);

namespace Crumbls\Tui\Laravel;

use Closure;

final class EventBus
{
    /** @var array<string, array<int, Closure>> */
    private array $listeners = [];

    /**
     * Register a listener for an event.
     */
    public function on(string $event, Closure $callback): self
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;

        return $this;
    }

    /**
     * Alias for on().
     */
    public function listen(string $event, Closure $callback): self
    {
        return $this->on($event, $callback);
    }

    /**
     * Register a one-time listener.
     */
    public function once(string $event, Closure $callback): self
    {
        $wrapper = function (array $data) use ($event, $callback, &$wrapper): void {
            $this->off($event, $wrapper);
            $callback($data);
        };

        return $this->on($event, $wrapper);
    }

    /**
     * Remove a listener.
     */
    public function off(string $event, Closure $callback): self
    {
        if (!isset($this->listeners[$event])) {
            return $this;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn (Closure $listener) => $listener !== $callback
        );

        return $this;
    }

    /**
     * Remove all listeners for an event.
     */
    public function removeAllListeners(?string $event = null): self
    {
        if ($event === null) {
            $this->listeners = [];
        } else {
            unset($this->listeners[$event]);
        }

        return $this;
    }

    /**
     * Emit an event with data.
     *
     * @param array<string, mixed> $data
     */
    public function emit(string $event, array $data = []): self
    {
        if (!isset($this->listeners[$event])) {
            return $this;
        }

        foreach ($this->listeners[$event] as $callback) {
            $callback($data);
        }

        return $this;
    }

    /**
     * Check if event has listeners.
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && count($this->listeners[$event]) > 0;
    }

    /**
     * Get listener count for an event.
     */
    public function listenerCount(string $event): int
    {
        return isset($this->listeners[$event]) ? count($this->listeners[$event]) : 0;
    }
}
