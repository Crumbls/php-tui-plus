<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\DataTable;

use Closure;

final class Column
{
    private function __construct(
        public readonly string $key,
        public string $label,
        public ?int $width = null,
        public ?int $minWidth = null,
        public ?int $maxWidth = null,
        public string $align = 'left',
        public ?Closure $format = null,
        public bool $sortable = false,
        public bool $visible = true,
        public bool $wrap = false,
    ) {
    }

    public static function make(string $key): self
    {
        $label = ucfirst($key);

        return new self(
            key: $key,
            label: $label,
        );
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function width(int $chars): self
    {
        $this->width = $chars;

        return $this;
    }

    public function minWidth(int $chars): self
    {
        $this->minWidth = $chars;

        return $this;
    }

    public function maxWidth(int $chars): self
    {
        $this->maxWidth = $chars;

        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function format(Closure $fn): self
    {
        $this->format = $fn;

        return $this;
    }

    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function visible(bool $visible = true): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function formatValue(mixed $value, array $row): string
    {
        if ($this->format !== null) {
            return (string) ($this->format)($value, $row);
        }

        return (string) ($value ?? '');
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getValue(array $row): string
    {
        $value = $row[$this->key] ?? null;

        return $this->formatValue($value, $row);
    }
}
