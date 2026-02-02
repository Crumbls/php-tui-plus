<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class SelectWidget implements Widget
{
    /** @var array<int|string, string> */
    private array $options = [];

    /** @var array<string, array<int|string, string>> */
    private array $optionGroups = [];

    private mixed $value = null;

    private bool $isOpen = false;

    private int $highlightedIndex = 0;

    private ?string $searchQuery = null;

    /** @var array<int|string, string> */
    private array $filteredOptions = [];

    private int $scrollOffset = 0;

    private ?string $label = null;

    private ?string $placeholder = null;

    private bool $disabled = false;

    private bool $required = false;

    private int $width = 30;

    private int $maxHeight = 10;

    private bool $searchable = false;

    private ?string $searchPlaceholder = null;

    private ?string $noResultsText = null;

    private bool $showLabel = true;

    private int $labelWidth = 0;

    private string $indicator = "\u{25BC}";

    private string $highlightStyle = 'inverse';

    private ?Closure $onChange = null;

    private ?Closure $onOpen = null;

    private ?Closure $onClose = null;

    private ?Closure $onSearch = null;

    /** @var array<string, string> */
    private array $keyBindings = [
        'enter' => 'toggleOrSelect',
        'space' => 'toggleOrSelect',
        'escape' => 'close',
        'up' => 'previousOption',
        'down' => 'nextOption',
        'home' => 'firstOption',
        'end' => 'lastOption',
        'tab' => 'selectAndNext',
    ];

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

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param array<int|string, string> $options
     */
    public function options(array $options): self
    {
        $this->options = $options;
        $this->filteredOptions = $options;

        return $this;
    }

    /**
     * @param array<string, array<int|string, string>> $groups
     */
    public function optionGroups(array $groups): self
    {
        $this->optionGroups = $groups;
        $merged = [];
        foreach ($groups as $groupOptions) {
            foreach ($groupOptions as $key => $value) {
                if (is_int($key)) {
                    $merged[] = $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }
        $this->options = $merged;
        $this->filteredOptions = $merged;

        return $this;
    }

    public function value(mixed $value): self
    {
        $this->value = $value;
        $this->updateHighlightedIndexFromValue();

        return $this;
    }

    public function placeholder(string $text): self
    {
        $this->placeholder = $text;

        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function width(int $chars): self
    {
        $this->width = $chars;

        return $this;
    }

    public function maxHeight(int $rows): self
    {
        $this->maxHeight = $rows;

        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function searchPlaceholder(string $text): self
    {
        $this->searchPlaceholder = $text;

        return $this;
    }

    public function noResultsText(string $text): self
    {
        $this->noResultsText = $text;

        return $this;
    }

    public function showLabel(bool $show = true): self
    {
        $this->showLabel = $show;

        return $this;
    }

    public function labelWidth(int $chars): self
    {
        $this->labelWidth = $chars;

        return $this;
    }

    public function indicator(string $char): self
    {
        $this->indicator = $char;

        return $this;
    }

    public function highlightStyle(string $style): self
    {
        $this->highlightStyle = $style;

        return $this;
    }

    // =========================================================================
    // Event Methods
    // =========================================================================

    public function onChange(Closure $fn): self
    {
        $this->onChange = $fn;

        return $this;
    }

    public function onOpen(Closure $fn): self
    {
        $this->onOpen = $fn;

        return $this;
    }

    public function onClose(Closure $fn): self
    {
        $this->onClose = $fn;

        return $this;
    }

    public function onSearch(Closure $fn): self
    {
        $this->onSearch = $fn;

        return $this;
    }

    /**
     * @param array<string, string> $bindings
     */
    public function keyBindings(array $bindings): self
    {
        $this->keyBindings = array_merge($this->keyBindings, $bindings);

        return $this;
    }

    // =========================================================================
    // Dropdown Control Methods
    // =========================================================================

    public function open(): self
    {
        if (!$this->isOpen) {
            $this->isOpen = true;
            if ($this->onOpen !== null) {
                ($this->onOpen)();
            }
        }

        return $this;
    }

    public function close(): self
    {
        if ($this->isOpen) {
            $this->isOpen = false;
            if ($this->onClose !== null) {
                ($this->onClose)();
            }
        }

        return $this;
    }

    public function toggle(): self
    {
        if ($this->isOpen) {
            $this->close();
        } else {
            $this->open();
        }

        return $this;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    // =========================================================================
    // Selection Methods
    // =========================================================================

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getLabel(): ?string
    {
        if ($this->value === null) {
            return $this->label;
        }

        $options = $this->getOptions();

        if (isset($options[$this->value])) {
            return $options[$this->value];
        }

        $index = array_search($this->value, array_values($options), true);
        if ($index !== false) {
            return $this->value;
        }

        return $this->label;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;
        $this->updateHighlightedIndexFromValue();

        return $this;
    }

    public function clear(): self
    {
        $this->value = null;
        $this->highlightedIndex = 0;

        return $this;
    }

    public function selectHighlighted(): self
    {
        $filteredOptions = $this->getFilteredOptions();
        $keys = array_keys($filteredOptions);
        $values = array_values($filteredOptions);

        if (isset($values[$this->highlightedIndex])) {
            $key = $keys[$this->highlightedIndex];
            $label = $values[$this->highlightedIndex];

            if (is_int($key)) {
                $this->value = $label;
            } else {
                $this->value = $key;
            }

            $this->close();

            if ($this->onChange !== null) {
                ($this->onChange)($this->value, $label);
            }
        }

        return $this;
    }

    // =========================================================================
    // Navigation Methods
    // =========================================================================

    public function previousOption(): self
    {
        $optionCount = count($this->getFilteredOptions());
        if ($optionCount === 0) {
            return $this;
        }

        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        } else {
            $this->highlightedIndex = $optionCount - 1;
        }
        $this->updateScrollOffset();

        return $this;
    }

    public function nextOption(): self
    {
        $optionCount = count($this->getFilteredOptions());
        if ($optionCount === 0) {
            return $this;
        }

        if ($this->highlightedIndex < $optionCount - 1) {
            $this->highlightedIndex++;
        } else {
            $this->highlightedIndex = 0;
        }
        $this->updateScrollOffset();

        return $this;
    }

    public function firstOption(): self
    {
        $this->highlightedIndex = 0;
        $this->scrollOffset = 0;

        return $this;
    }

    public function lastOption(): self
    {
        $optionCount = count($this->getFilteredOptions());
        if ($optionCount > 0) {
            $this->highlightedIndex = $optionCount - 1;
            $this->updateScrollOffset();
        }

        return $this;
    }

    public function highlightOption(int $index): self
    {
        $optionCount = count($this->getFilteredOptions());
        if ($index >= 0 && $index < $optionCount) {
            $this->highlightedIndex = $index;
            $this->updateScrollOffset();
        }

        return $this;
    }

    public function getHighlightedIndex(): int
    {
        return $this->highlightedIndex;
    }

    // =========================================================================
    // Search Methods
    // =========================================================================

    public function search(string $query): self
    {
        $this->searchQuery = $query;
        $this->filterOptions();

        if ($this->onSearch !== null) {
            ($this->onSearch)($query);
        }

        return $this;
    }

    public function clearSearch(): self
    {
        $this->searchQuery = null;
        $this->filteredOptions = $this->options;
        $this->highlightedIndex = 0;
        $this->scrollOffset = 0;

        return $this;
    }

    /**
     * @return array<int|string, string>
     */
    public function getFilteredOptions(): array
    {
        return $this->filteredOptions;
    }

    // =========================================================================
    // Options Methods
    // =========================================================================

    /**
     * @return array<int|string, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<int|string, string> $options
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        $this->filteredOptions = $options;

        return $this;
    }

    public function addOption(mixed $value, ?string $label = null): self
    {
        if ($label !== null) {
            $this->options[$value] = $label;
            $this->filteredOptions[$value] = $label;
        } else {
            $this->options[] = $value;
            $this->filteredOptions[] = $value;
        }

        return $this;
    }

    public function removeOption(mixed $value): self
    {
        foreach ($this->options as $key => $optionValue) {
            if ($key === $value || $optionValue === $value) {
                unset($this->options[$key]);
                unset($this->filteredOptions[$key]);
                break;
            }
        }
        $this->options = array_values($this->options);
        $this->filteredOptions = array_values($this->filteredOptions);

        return $this;
    }

    public function hasOption(mixed $value): bool
    {
        if (array_key_exists($value, $this->options)) {
            return true;
        }

        return in_array($value, $this->options, true);
    }

    // =========================================================================
    // Getter Methods
    // =========================================================================

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchPlaceholder(): ?string
    {
        return $this->searchPlaceholder;
    }

    public function getNoResultsText(): ?string
    {
        return $this->noResultsText;
    }

    public function isShowLabel(): bool
    {
        return $this->showLabel;
    }

    public function getLabelWidth(): int
    {
        return $this->labelWidth;
    }

    public function getIndicator(): string
    {
        return $this->indicator;
    }

    public function getHighlightStyle(): string
    {
        return $this->highlightStyle;
    }

    /**
     * @return array<string, string>
     */
    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    public function getScrollOffset(): int
    {
        return $this->scrollOffset;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function getOptionGroups(): array
    {
        return $this->optionGroups;
    }

    // =========================================================================
    // Private Methods
    // =========================================================================

    private function filterOptions(): void
    {
        if ($this->searchQuery === null || $this->searchQuery === '') {
            $this->filteredOptions = $this->options;

            return;
        }

        $query = mb_strtolower($this->searchQuery);
        $this->filteredOptions = [];

        foreach ($this->options as $key => $value) {
            $searchTarget = is_string($key) ? $value : $value;
            if (mb_stripos($searchTarget, $query) !== false) {
                $this->filteredOptions[$key] = $value;
            }
        }

        $this->highlightedIndex = 0;
        $this->scrollOffset = 0;
    }

    private function updateHighlightedIndexFromValue(): void
    {
        if ($this->value === null) {
            return;
        }

        $index = 0;
        foreach ($this->filteredOptions as $key => $optionValue) {
            if ($key === $this->value || $optionValue === $this->value) {
                $this->highlightedIndex = $index;
                $this->updateScrollOffset();

                return;
            }
            $index++;
        }
    }

    private function updateScrollOffset(): void
    {
        if ($this->highlightedIndex < $this->scrollOffset) {
            $this->scrollOffset = $this->highlightedIndex;
        } elseif ($this->highlightedIndex >= $this->scrollOffset + $this->maxHeight) {
            $this->scrollOffset = $this->highlightedIndex - $this->maxHeight + 1;
        }
    }
}
