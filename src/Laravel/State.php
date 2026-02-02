<?php

declare(strict_types=1);

namespace Crumbls\Tui\Laravel;

use Closure;

final class State
{
    /** @var array<string, mixed> */
    private array $data = [];

    /** @var array<string, array<int, Closure>> */
    private array $watchers = [];

    /**
     * Get a value from state.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a value in state.
     */
    public function set(string $key, mixed $value): self
    {
        $oldValue = $this->data[$key] ?? null;
        $this->data[$key] = $value;

        if ($oldValue !== $value) {
            $this->notifyWatchers($key, $value, $oldValue);
        }

        return $this;
    }

    /**
     * Check if a key exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove a key from state.
     */
    public function forget(string $key): self
    {
        $oldValue = $this->data[$key] ?? null;
        unset($this->data[$key]);

        $this->notifyWatchers($key, null, $oldValue);

        return $this;
    }

    /**
     * Get all state data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Merge data into state.
     *
     * @param array<string, mixed> $data
     */
    public function merge(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Clear all state.
     */
    public function clear(): self
    {
        $keys = array_keys($this->data);
        $this->data = [];

        foreach ($keys as $key) {
            $this->notifyWatchers($key, null, null);
        }

        return $this;
    }

    /**
     * Watch for changes to a key.
     */
    public function watch(string $key, Closure $callback): self
    {
        if (!isset($this->watchers[$key])) {
            $this->watchers[$key] = [];
        }

        $this->watchers[$key][] = $callback;

        return $this;
    }

    /**
     * Stop watching a key.
     */
    public function unwatch(string $key, ?Closure $callback = null): self
    {
        if (!isset($this->watchers[$key])) {
            return $this;
        }

        if ($callback === null) {
            unset($this->watchers[$key]);
        } else {
            $this->watchers[$key] = array_filter(
                $this->watchers[$key],
                fn (Closure $watcher) => $watcher !== $callback
            );
        }

        return $this;
    }

    /**
     * Increment a numeric value.
     */
    public function increment(string $key, int|float $amount = 1): self
    {
        $current = $this->get($key, 0);

        return $this->set($key, $current + $amount);
    }

    /**
     * Decrement a numeric value.
     */
    public function decrement(string $key, int|float $amount = 1): self
    {
        return $this->increment($key, -$amount);
    }

    /**
     * Toggle a boolean value.
     */
    public function toggle(string $key): self
    {
        return $this->set($key, !$this->get($key, false));
    }

    /**
     * Push a value onto an array.
     */
    public function push(string $key, mixed $value): self
    {
        $array = $this->get($key, []);

        if (!is_array($array)) {
            $array = [$array];
        }

        $array[] = $value;

        return $this->set($key, $array);
    }

    private function notifyWatchers(string $key, mixed $newValue, mixed $oldValue): void
    {
        if (!isset($this->watchers[$key])) {
            return;
        }

        foreach ($this->watchers[$key] as $callback) {
            $callback($newValue, $oldValue, $key);
        }
    }
}
