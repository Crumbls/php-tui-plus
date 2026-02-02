<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Widget\Widget;

final class ProgressBarWidget implements Widget
{
    private float $progress = 0.0;

    private ?int $current = null;

    private ?int $total = null;

    private bool $indeterminate = false;

    private string $label = '';

    private string $state = 'running';

    private ?int $startTime = null;

    private int $animationFrame = 0;

    private bool $showPercent = true;

    private bool $showCount = false;

    private bool $showEta = false;

    private ?string $format = null;

    private ?int $width = null;

    private string $style = 'default';

    private string $filledChar = "\u{2588}";

    private string $emptyChar = "\u{2591}";

    private bool $showBrackets = false;

    private string $color = '';

    private int $animationSpeed = 100;

    private string $completeText = 'Complete!';

    private string $errorText = 'Failed';

    private ?Closure $onComplete = null;

    private ?Closure $onProgress = null;

    private ?Closure $onCancel = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    // =========================================================================
    // Progress Methods
    // =========================================================================

    public function progress(float $percent): self
    {
        if ($percent >= 2.0 && $percent <= 100.0) {
            $percent = $percent / 100.0;
        }

        $this->progress = max(0.0, min(1.0, $percent));

        if ($this->onProgress !== null) {
            ($this->onProgress)($this->progress);
        }

        return $this;
    }

    public function setProgress(float $percent): self
    {
        return $this->progress($percent);
    }

    public function current(int $current): self
    {
        $this->current = max(0, $current);
        $this->updateProgressFromCurrentTotal();

        return $this;
    }

    public function setCurrent(int $current): self
    {
        return $this->current($current);
    }

    public function total(int $total): self
    {
        $this->total = $total;
        $this->updateProgressFromCurrentTotal();

        return $this;
    }

    public function setTotal(int $total): self
    {
        return $this->total($total);
    }

    public function increment(int $amount = 1): self
    {
        if ($this->current === null) {
            $this->current = 0;
        }

        $this->current += $amount;
        $this->updateProgressFromCurrentTotal();

        return $this;
    }

    public function indeterminate(bool $indeterminate = true): self
    {
        $this->indeterminate = $indeterminate;

        return $this;
    }

    private function updateProgressFromCurrentTotal(): void
    {
        if ($this->total !== null && $this->total > 0 && $this->current !== null) {
            $this->progress = max(0.0, min(1.0, $this->current / $this->total));

            if ($this->onProgress !== null) {
                ($this->onProgress)($this->progress);
            }
        }
    }

    // =========================================================================
    // Label Methods
    // =========================================================================

    public function label(string $text): self
    {
        $this->label = $text;

        return $this;
    }

    public function showPercent(bool $show = true): self
    {
        $this->showPercent = $show;

        return $this;
    }

    public function showCount(bool $show = true): self
    {
        $this->showCount = $show;

        return $this;
    }

    public function showEta(bool $show = true): self
    {
        $this->showEta = $show;

        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    // =========================================================================
    // Display Options
    // =========================================================================

    public function width(int $chars): self
    {
        $this->width = $chars;

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function filledChar(string $char): self
    {
        $this->filledChar = $char;

        return $this;
    }

    public function emptyChar(string $char): self
    {
        $this->emptyChar = $char;

        return $this;
    }

    public function showBrackets(bool $show = true): self
    {
        $this->showBrackets = $show;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function animationSpeed(int $ms): self
    {
        $this->animationSpeed = $ms;

        return $this;
    }

    // =========================================================================
    // State Methods
    // =========================================================================

    public function state(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function completeText(string $text): self
    {
        $this->completeText = $text;

        return $this;
    }

    public function errorText(string $text): self
    {
        $this->errorText = $text;

        return $this;
    }

    public function start(): self
    {
        $this->startTime = time();
        $this->state = 'running';

        return $this;
    }

    public function pause(): self
    {
        $this->state = 'paused';

        return $this;
    }

    public function resume(): self
    {
        $this->state = 'running';

        return $this;
    }

    public function complete(): self
    {
        $this->state = 'complete';
        $this->progress = 1.0;

        if ($this->onComplete !== null) {
            ($this->onComplete)();
        }

        return $this;
    }

    public function error(): self
    {
        $this->state = 'error';

        return $this;
    }

    public function reset(): self
    {
        $this->progress = 0.0;
        $this->current = 0;
        $this->startTime = null;
        $this->state = 'running';
        $this->animationFrame = 0;

        return $this;
    }

    public function cancel(): self
    {
        if ($this->onCancel !== null) {
            ($this->onCancel)();
        }

        return $this;
    }

    // =========================================================================
    // Event Methods
    // =========================================================================

    public function onComplete(Closure $fn): self
    {
        $this->onComplete = $fn;

        return $this;
    }

    public function onProgress(Closure $fn): self
    {
        $this->onProgress = $fn;

        return $this;
    }

    public function onCancel(Closure $fn): self
    {
        $this->onCancel = $fn;

        return $this;
    }

    // =========================================================================
    // Animation Methods
    // =========================================================================

    public function tick(): self
    {
        $this->animationFrame++;

        return $this;
    }

    // =========================================================================
    // Getters
    // =========================================================================

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function getCurrent(): ?int
    {
        return $this->current;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function isIndeterminate(): bool
    {
        return $this->indeterminate;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    public function getAnimationFrame(): int
    {
        return $this->animationFrame;
    }

    public function getShowPercent(): bool
    {
        return $this->showPercent;
    }

    public function getShowCount(): bool
    {
        return $this->showCount;
    }

    public function getShowEta(): bool
    {
        return $this->showEta;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function getFilledChar(): string
    {
        return $this->filledChar;
    }

    public function getEmptyChar(): string
    {
        return $this->emptyChar;
    }

    public function hasShowBrackets(): bool
    {
        return $this->showBrackets;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getAnimationSpeed(): int
    {
        return $this->animationSpeed;
    }

    public function getCompleteText(): string
    {
        return $this->completeText;
    }

    public function getErrorText(): string
    {
        return $this->errorText;
    }

    public function isComplete(): bool
    {
        return $this->progress >= 1.0;
    }

    // =========================================================================
    // Timing Methods
    // =========================================================================

    public function getElapsed(): int
    {
        if ($this->startTime === null) {
            return 0;
        }

        return time() - $this->startTime;
    }

    public function getEta(): ?int
    {
        if ($this->startTime === null || $this->progress <= 0) {
            return null;
        }

        $elapsed = $this->getElapsed();

        if ($elapsed <= 0) {
            return null;
        }

        $remainingProgress = 1.0 - $this->progress;
        $rate = $this->progress / $elapsed;

        if ($rate <= 0) {
            return null;
        }

        return (int) ceil($remainingProgress / $rate);
    }

    public function getRate(): float
    {
        if ($this->startTime === null) {
            return 0.0;
        }

        $elapsed = $this->getElapsed();

        if ($elapsed <= 0) {
            return 0.0;
        }

        if ($this->current !== null) {
            return $this->current / $elapsed;
        }

        return 0.0;
    }
}
