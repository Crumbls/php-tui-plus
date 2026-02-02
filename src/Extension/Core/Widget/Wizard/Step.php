<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Wizard;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class Step
{
    private string $id;

    private string $title = '';

    private string $description = '';

    private Closure|Widget|null $component = null;

    private ?Closure $validate = null;

    private ?Closure $beforeEnter = null;

    private ?Closure $beforeLeave = null;

    private ?Closure $onEnter = null;

    private ?Closure $onLeave = null;

    private bool $optional = false;

    private Closure|bool $hidden = false;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function make(string $id): self
    {
        return new self($id);
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $text): self
    {
        $this->description = $text;

        return $this;
    }

    public function component(Closure|Widget $content): self
    {
        $this->component = $content;

        return $this;
    }

    public function validate(Closure $fn): self
    {
        $this->validate = $fn;

        return $this;
    }

    public function beforeEnter(Closure $fn): self
    {
        $this->beforeEnter = $fn;

        return $this;
    }

    public function beforeLeave(Closure $fn): self
    {
        $this->beforeLeave = $fn;

        return $this;
    }

    public function onEnter(Closure $fn): self
    {
        $this->onEnter = $fn;

        return $this;
    }

    public function onLeave(Closure $fn): self
    {
        $this->onLeave = $fn;

        return $this;
    }

    public function optional(bool $optional = true): self
    {
        $this->optional = $optional;

        return $this;
    }

    public function hidden(Closure|bool $hidden = true): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasComponent(): bool
    {
        return $this->component !== null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderComponent(array $data): Widget|null
    {
        if ($this->component === null) {
            return null;
        }

        if ($this->component instanceof Widget) {
            return $this->component;
        }

        return ($this->component)($data);
    }

    public function hasValidation(): bool
    {
        return $this->validate !== null;
    }

    /**
     * @param array<string, mixed> $data
     * @return bool|string True if valid, or error message string
     */
    public function runValidation(array $data): bool|string
    {
        if ($this->validate === null) {
            return true;
        }

        return ($this->validate)($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function canEnter(array $data, ?string $fromStep): bool
    {
        if ($this->beforeEnter === null) {
            return true;
        }

        return ($this->beforeEnter)($data, $fromStep);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function canLeave(array $data, ?string $toStep): bool
    {
        if ($this->beforeLeave === null) {
            return true;
        }

        return ($this->beforeLeave)($data, $toStep);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function triggerOnEnter(array $data): void
    {
        if ($this->onEnter !== null) {
            ($this->onEnter)($data);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function triggerOnLeave(array $data): void
    {
        if ($this->onLeave !== null) {
            ($this->onLeave)($data);
        }
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function isHidden(): bool
    {
        if ($this->hidden instanceof Closure) {
            return ($this->hidden)();
        }

        return $this->hidden;
    }
}
