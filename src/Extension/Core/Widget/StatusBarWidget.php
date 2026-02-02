<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Widget\Widget;

final class StatusBarWidget implements Widget
{
    /** @var array<string, string> */
    private array $hints = [];

    private ?string $leftContent = null;

    private ?string $centerContent = null;

    private ?string $rightContent = null;

    private string $position = 'bottom';

    private string $context = 'default';

    /** @var array<string, array<string, string>> */
    private array $contexts = [];

    /** @var array<int, string> */
    private array $breadcrumbs = [];

    private string $breadcrumbSeparator = ' > ';

    private bool $border = false;

    private string $separator = '  ';

    private int $padding = 1;

    private string $style = 'default';

    /** @var array<string, string> */
    private array $keyMappings = [
        'enter' => "\u{21B5}",
        'escape' => 'esc',
        'space' => "\u{2423}",
        'up' => "\u{2191}",
        'down' => "\u{2193}",
        'left' => "\u{2190}",
        'right' => "\u{2192}",
        'tab' => "\u{21E5}",
    ];

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    // =========================================================================
    // Content Configuration Methods
    // =========================================================================

    /**
     * @param array<string, string> $hints
     */
    public function hints(array $hints): self
    {
        $this->hints = $hints;

        return $this;
    }

    public function left(string $content): self
    {
        $this->leftContent = $content;

        return $this;
    }

    public function center(string $content): self
    {
        $this->centerContent = $content;

        return $this;
    }

    public function right(string $content): self
    {
        $this->rightContent = $content;

        return $this;
    }

    // =========================================================================
    // Display Configuration Methods
    // =========================================================================

    public function position(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function showBorder(bool $show = true): self
    {
        $this->border = $show;

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function padding(int $chars): self
    {
        $this->padding = $chars;

        return $this;
    }

    public function separator(string $sep): self
    {
        $this->separator = $sep;

        return $this;
    }

    // =========================================================================
    // Context Methods
    // =========================================================================

    public function context(string $context): self
    {
        $this->context = $context;
        if (isset($this->contexts[$context])) {
            $this->hints = $this->contexts[$context];
        }

        return $this;
    }

    /**
     * @param array<string, array<string, string>> $contexts
     */
    public function contexts(array $contexts): self
    {
        $this->contexts = $contexts;

        return $this;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;
        if (isset($this->contexts[$context])) {
            $this->hints = $this->contexts[$context];
        }

        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param array<string, string> $hints
     */
    public function addContext(string $name, array $hints): self
    {
        $this->contexts[$name] = $hints;

        return $this;
    }

    public function hasContext(string $name): bool
    {
        return isset($this->contexts[$name]);
    }

    // =========================================================================
    // Breadcrumb Methods
    // =========================================================================

    /**
     * @param array<int, string> $items
     */
    public function breadcrumbs(array $items): self
    {
        $this->breadcrumbs = $items;

        return $this;
    }

    public function breadcrumbSeparator(string $separator): self
    {
        $this->breadcrumbSeparator = $separator;

        return $this;
    }

    /**
     * @param array<int, string> $items
     */
    public function setBreadcrumbs(array $items): self
    {
        $this->breadcrumbs = $items;

        return $this;
    }

    public function pushBreadcrumb(string $item): self
    {
        $this->breadcrumbs[] = $item;

        return $this;
    }

    public function popBreadcrumb(): self
    {
        array_pop($this->breadcrumbs);

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    public function getBreadcrumbSeparator(): string
    {
        return $this->breadcrumbSeparator;
    }

    // =========================================================================
    // Hint Methods
    // =========================================================================

    /**
     * @param array<string, string> $hints
     */
    public function setHints(array $hints): self
    {
        $this->hints = $hints;

        return $this;
    }

    public function addHint(string $key, string $action): self
    {
        $this->hints[$key] = $action;

        return $this;
    }

    public function removeHint(string $key): self
    {
        unset($this->hints[$key]);

        return $this;
    }

    public function clearHints(): self
    {
        $this->hints = [];

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    /**
     * @return array<int, array{key: string, action: string}>
     */
    public function getFormattedHints(): array
    {
        $formatted = [];
        foreach ($this->hints as $key => $action) {
            $formatted[] = [
                'key' => $this->formatKey($key),
                'action' => $action,
            ];
        }

        return $formatted;
    }

    public function formatKey(string $key): string
    {
        if (isset($this->keyMappings[$key])) {
            return $this->keyMappings[$key];
        }

        if (str_starts_with($key, 'ctrl+')) {
            $char = mb_strtoupper(mb_substr($key, 5));

            return "^{$char}";
        }

        return $key;
    }

    // =========================================================================
    // Section Getters
    // =========================================================================

    public function getLeft(): ?string
    {
        return $this->leftContent;
    }

    public function setLeft(string $content): self
    {
        $this->leftContent = $content;

        return $this;
    }

    public function getCenter(): ?string
    {
        return $this->centerContent;
    }

    public function setCenter(string $content): self
    {
        $this->centerContent = $content;

        return $this;
    }

    public function getRight(): ?string
    {
        return $this->rightContent;
    }

    public function setRight(string $content): self
    {
        $this->rightContent = $content;

        return $this;
    }

    // =========================================================================
    // Display Getters
    // =========================================================================

    public function getPosition(): string
    {
        return $this->position;
    }

    public function hasBorder(): bool
    {
        return $this->border;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getPadding(): int
    {
        return $this->padding;
    }

    public function getStyle(): string
    {
        return $this->style;
    }
}
