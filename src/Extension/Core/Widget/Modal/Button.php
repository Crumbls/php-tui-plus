<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Modal;

final class Button
{
    private string $id;

    private string $label = '';

    private string $style = 'primary';

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function make(string $id): self
    {
        return new self($id);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getStyle(): string
    {
        return $this->style;
    }
}
