<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\SelectableTable;

use Closure;

final class Column
{
    private function __construct(
        public readonly string $key,
        public string $label,
        public ?int $width = null,
        public ?int $minWidth = null,
        public ?int $maxWidth = null,
        public ?int $flex = null,
        public string $align = 'left',
        public ?Closure $format = null,
        public ?string $static = null,
        public bool $visible = true,
        public bool $truncate = true,
    ) {
    }

    public static function make(string $key): self
    {
        return new self(
            key: $key,
            label: ucfirst($key),
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

    public function flex(int $weight = 1): self
    {
        $this->flex = $weight;

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

    public function static(string $value): self
    {
        $this->static = $value;

        return $this;
    }

    public function visible(bool $visible = true): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function truncate(bool $truncate = true): self
    {
        $this->truncate = $truncate;

        return $this;
    }

    public function getValue(array $row): string
    {
        if ($this->static !== null) {
            return $this->static;
        }

        $value = $row[$this->key] ?? '';

        if ($this->format !== null) {
            $value = ($this->format)($value, $row);
        }

        return (string) $value;
    }
}
