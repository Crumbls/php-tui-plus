<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\ContextPanel;

use Closure;

final class Section
{
    private string $id;

    private string $title = '';

    /** @var array<string, Closure> */
    private array $fields = [];

    private Closure|null $component = null;

    private Closure|bool $visible = true;

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

    /**
     * @param array<string, Closure> $fields
     */
    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function component(Closure $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function visible(Closure|bool $visible): self
    {
        $this->visible = $visible;

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

    /**
     * @return array<string, Closure>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function hasFields(): bool
    {
        return count($this->fields) > 0;
    }

    public function hasComponent(): bool
    {
        return $this->component !== null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function isVisible(array $data): bool
    {
        if ($this->visible instanceof Closure) {
            return (bool) ($this->visible)($data);
        }

        return $this->visible;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function resolveFields(array $data): array
    {
        $resolved = [];

        foreach ($this->fields as $label => $callback) {
            $value = $callback($data);
            $resolved[$label] = $value === null ? '' : (string) $value;
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderContent(array $data): ?string
    {
        if ($this->component === null) {
            return null;
        }

        $result = ($this->component)($data);

        return $result === null ? null : (string) $result;
    }

    public function isEmpty(): bool
    {
        return !$this->hasFields() && !$this->hasComponent();
    }
}
