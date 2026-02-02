<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Extension\Core\Widget\ContextPanel\Section;
use Crumbls\Tui\Widget\Widget;

final class ContextPanelWidget implements Widget
{
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    /** @var list<Section> */
    private array $sections = [];

    /** @var array<string, Closure> */
    private array $fields = [];

    private string|Closure|null $content = null;

    private ?Closure $contentCallback = null;

    private string|Closure|null $title = null;

    private bool $showTitle = true;

    private bool $showBorder = true;

    private ?int $width = null;

    private ?int $height = null;

    private string $position = 'right';

    private string $emptyText = 'No item selected';

    private bool $scrollable = true;

    private int $scrollOffset = 0;

    private object|null $boundSource = null;

    private ?string $boundEvent = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function title(string|Closure $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function showTitle(bool $show = true): self
    {
        $this->showTitle = $show;

        return $this;
    }

    public function showBorder(bool $show = true): self
    {
        $this->showBorder = $show;

        return $this;
    }

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

    public function position(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function emptyText(string $text): self
    {
        $this->emptyText = $text;

        return $this;
    }

    public function scrollable(bool $scrollable = true): self
    {
        $this->scrollable = $scrollable;

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function contentFrom(Closure $fn): self
    {
        $this->contentCallback = $fn;

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

    /**
     * @param list<Section> $sections
     */
    public function sections(array $sections): self
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function bind(object $source, string $event = 'onHighlight'): self
    {
        $this->boundSource = $source;
        $this->boundEvent = $event;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(?array $data): self
    {
        $this->data = $data;
        $this->scrollOffset = 0;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    public function hasData(): bool
    {
        return $this->data !== null;
    }

    public function clearData(): self
    {
        $this->data = null;
        $this->scrollOffset = 0;

        return $this;
    }

    public function updateField(string $key, mixed $value): self
    {
        if ($this->data === null) {
            $this->data = [];
        }

        $this->data[$key] = $value;

        return $this;
    }

    public function getContent(): string|null
    {
        if (!$this->hasData()) {
            return null;
        }

        if ($this->contentCallback !== null) {
            return ($this->contentCallback)($this->data);
        }

        if (is_string($this->content)) {
            return $this->content;
        }

        return null;
    }

    public function getTitle(): ?string
    {
        if ($this->title === null) {
            return null;
        }

        if ($this->title instanceof Closure) {
            return ($this->title)($this->data ?? []);
        }

        return $this->title;
    }

    /**
     * @return list<Section>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @return list<Section>
     */
    public function getVisibleSections(): array
    {
        if (!$this->hasData()) {
            return [];
        }

        $data = $this->data ?? [];

        return array_values(array_filter(
            $this->sections,
            fn (Section $section) => $section->isVisible($data) && !$section->isEmpty()
        ));
    }

    /**
     * @return array<string, Closure>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, string>
     */
    public function resolveFields(): array
    {
        if (!$this->hasData()) {
            return [];
        }

        $resolved = [];
        $data = $this->data ?? [];

        foreach ($this->fields as $label => $callback) {
            $value = $callback($data);
            $resolved[$label] = $value === null ? '' : (string) $value;
        }

        return $resolved;
    }

    public function scrollUp(int $lines = 1): self
    {
        $this->scrollOffset = max(0, $this->scrollOffset - $lines);

        return $this;
    }

    public function scrollDown(int $lines = 1): self
    {
        $this->scrollOffset += $lines;

        return $this;
    }

    public function scrollToTop(): self
    {
        $this->scrollOffset = 0;

        return $this;
    }

    public function scrollToBottom(): self
    {
        $contentHeight = $this->calculateContentHeight();
        $viewHeight = $this->height ?? 10;

        $this->scrollOffset = max(0, $contentHeight - $viewHeight);

        return $this;
    }

    public function canScroll(): bool
    {
        if (!$this->scrollable) {
            return false;
        }

        $contentHeight = $this->calculateContentHeight();
        $viewHeight = $this->height ?? 10;

        return $contentHeight > $viewHeight;
    }

    public function getScrollOffset(): int
    {
        return $this->scrollOffset;
    }

    public function showsTitle(): bool
    {
        return $this->showTitle;
    }

    public function showsBorder(): bool
    {
        return $this->showBorder;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getEmptyText(): string
    {
        return $this->emptyText;
    }

    public function isScrollable(): bool
    {
        return $this->scrollable;
    }

    public function getBoundSource(): ?object
    {
        return $this->boundSource;
    }

    public function getBoundEvent(): ?string
    {
        return $this->boundEvent;
    }

    private function calculateContentHeight(): int
    {
        $height = 0;

        if (count($this->fields) > 0) {
            $height += count($this->fields);
        }

        foreach ($this->getVisibleSections() as $section) {
            $height += 2;
            $height += count($section->getFields());

            if ($section->hasComponent()) {
                $height += 3;
            }
        }

        return $height;
    }
}
