<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Extension\Core\Widget\Wizard\Step;
use Crumbls\Tui\Widget\Widget;

final class WizardWidget implements Widget
{
    private ?string $title = null;

    /** @var array<int, Step> */
    private array $steps = [];

    private int $currentStepIndex = 0;

    /** @var array<string, mixed> */
    private array $data = [];

    /** @var array<string, string|null> */
    private array $stepErrors = [];

    /** @var array<string, bool> */
    private array $completedSteps = [];

    private ?int $width = null;

    private ?int $height = null;

    private bool $showStepIndicator = true;

    private bool $showStepTitles = false;

    private bool $allowBackNavigation = true;

    private bool $allowSkip = false;

    private bool $linear = true;

    private bool $confirmCancel = false;

    private bool $showCancel = true;

    private string $backButtonLabel = 'Back';

    private string $nextButtonLabel = 'Next';

    private string $finishButtonLabel = 'Finish';

    private string $cancelButtonLabel = 'Cancel';

    private ?Closure $onChange = null;

    private ?Closure $onStepChange = null;

    private ?Closure $onComplete = null;

    private ?Closure $onCancel = null;

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

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array<int, Step> $steps
     */
    public function steps(array $steps): self
    {
        $this->steps = $steps;

        return $this;
    }

    public function currentStep(int|string $step): self
    {
        if (is_int($step)) {
            $this->currentStepIndex = $step;
        } else {
            foreach ($this->steps as $index => $s) {
                if ($s->getId() === $step) {
                    $this->currentStepIndex = $index;
                    break;
                }
            }
        }

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

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function showStepIndicator(bool $show = true): self
    {
        $this->showStepIndicator = $show;

        return $this;
    }

    public function showStepTitles(bool $show = true): self
    {
        $this->showStepTitles = $show;

        return $this;
    }

    public function allowBackNavigation(bool $allow = true): self
    {
        $this->allowBackNavigation = $allow;

        return $this;
    }

    public function allowSkip(bool $allow = true): self
    {
        $this->allowSkip = $allow;

        return $this;
    }

    public function linear(bool $linear = true): self
    {
        $this->linear = $linear;

        return $this;
    }

    public function confirmCancel(bool $confirm = true): self
    {
        $this->confirmCancel = $confirm;

        return $this;
    }

    public function showCancel(bool $show = true): self
    {
        $this->showCancel = $show;

        return $this;
    }

    public function backButton(string $label): self
    {
        $this->backButtonLabel = $label;

        return $this;
    }

    public function nextButton(string $label): self
    {
        $this->nextButtonLabel = $label;

        return $this;
    }

    public function finishButton(string $label): self
    {
        $this->finishButtonLabel = $label;

        return $this;
    }

    public function cancelButton(string $label): self
    {
        $this->cancelButtonLabel = $label;

        return $this;
    }

    // =========================================================================
    // Event Handler Methods
    // =========================================================================

    public function onChange(Closure $fn): self
    {
        $this->onChange = $fn;

        return $this;
    }

    public function onStepChange(Closure $fn): self
    {
        $this->onStepChange = $fn;

        return $this;
    }

    public function onComplete(Closure $fn): self
    {
        $this->onComplete = $fn;

        return $this;
    }

    public function onCancel(Closure $fn): self
    {
        $this->onCancel = $fn;

        return $this;
    }

    // =========================================================================
    // Navigation Methods
    // =========================================================================

    public function next(): void
    {
        if (!$this->canGoNext()) {
            return;
        }

        $currentStep = $this->getCurrentStep();

        $validationResult = $currentStep->runValidation($this->data);
        if ($validationResult !== true) {
            $this->stepErrors[$currentStep->getId()] = is_string($validationResult) ? $validationResult : 'Validation failed';

            return;
        }

        if (!$currentStep->canLeave($this->data, $this->getNextStepId())) {
            return;
        }

        $nextIndex = $this->findNextVisibleStepIndex();
        if ($nextIndex === null) {
            return;
        }

        $nextStep = $this->steps[$nextIndex];
        if (!$nextStep->canEnter($this->data, $currentStep->getId())) {
            return;
        }

        $fromIndex = $this->currentStepIndex;

        $this->completedSteps[$currentStep->getId()] = true;
        $currentStep->triggerOnLeave($this->data);

        $this->currentStepIndex = $nextIndex;
        $nextStep->triggerOnEnter($this->data);

        if ($this->onStepChange !== null) {
            ($this->onStepChange)($fromIndex, $this->currentStepIndex, $this->data);
        }
    }

    public function back(): void
    {
        if (!$this->canGoBack()) {
            return;
        }

        $currentStep = $this->getCurrentStep();
        $prevIndex = $this->findPreviousVisibleStepIndex();

        if ($prevIndex === null) {
            return;
        }

        $fromIndex = $this->currentStepIndex;
        $this->currentStepIndex = $prevIndex;

        if ($this->onStepChange !== null) {
            ($this->onStepChange)($fromIndex, $this->currentStepIndex, $this->data);
        }
    }

    public function goToStep(int|string $step): void
    {
        $targetIndex = $this->resolveStepIndex($step);

        if ($targetIndex === null) {
            return;
        }

        if ($this->linear && $targetIndex > $this->currentStepIndex) {
            return;
        }

        $fromIndex = $this->currentStepIndex;
        $this->currentStepIndex = $targetIndex;

        if ($this->onStepChange !== null && $fromIndex !== $targetIndex) {
            ($this->onStepChange)($fromIndex, $this->currentStepIndex, $this->data);
        }
    }

    public function canGoBack(): bool
    {
        if (!$this->allowBackNavigation) {
            return false;
        }

        return $this->findPreviousVisibleStepIndex() !== null;
    }

    public function canGoNext(): bool
    {
        return $this->findNextVisibleStepIndex() !== null;
    }

    public function isFirstStep(): bool
    {
        return $this->findPreviousVisibleStepIndex() === null;
    }

    public function isLastStep(): bool
    {
        return $this->findNextVisibleStepIndex() === null;
    }

    // =========================================================================
    // Data Methods
    // =========================================================================

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;

        if ($this->onChange !== null) {
            ($this->onChange)($key, $value, $this->data);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function mergeData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function clearData(): void
    {
        $this->data = [];
    }

    // =========================================================================
    // Validation Methods
    // =========================================================================

    public function validate(): bool
    {
        $currentStep = $this->getCurrentStep();
        $result = $currentStep->runValidation($this->data);

        if ($result === true) {
            $this->stepErrors[$currentStep->getId()] = null;

            return true;
        }

        $this->stepErrors[$currentStep->getId()] = is_string($result) ? $result : 'Validation failed';

        return false;
    }

    public function getStepError(string $stepId): ?string
    {
        return $this->stepErrors[$stepId] ?? null;
    }

    public function isStepComplete(string $stepId): bool
    {
        return $this->completedSteps[$stepId] ?? false;
    }

    public function isStepValid(string $stepId): bool
    {
        $step = $this->getStep($stepId);

        if ($step === null) {
            return false;
        }

        return $step->runValidation($this->data) === true;
    }

    // =========================================================================
    // Control Methods
    // =========================================================================

    public function complete(): void
    {
        if ($this->onComplete !== null) {
            ($this->onComplete)($this->data);
        }
    }

    public function cancel(): void
    {
        if ($this->onCancel !== null) {
            ($this->onCancel)($this->data);
        }
    }

    // =========================================================================
    // Step Accessor Methods
    // =========================================================================

    public function getCurrentStep(): Step
    {
        return $this->steps[$this->currentStepIndex];
    }

    public function getCurrentStepIndex(): int
    {
        return $this->currentStepIndex;
    }

    /**
     * @return array<int, Step>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getStep(string $id): ?Step
    {
        foreach ($this->steps as $step) {
            if ($step->getId() === $id) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{id: string, title: string}>
     */
    public function getVisibleSteps(): array
    {
        $visible = [];

        foreach ($this->steps as $step) {
            if (!$step->isHidden()) {
                $visible[] = [
                    'id' => $step->getId(),
                    'title' => $step->getTitle(),
                ];
            }
        }

        return $visible;
    }

    // =========================================================================
    // Getter Methods
    // =========================================================================

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function showsStepIndicator(): bool
    {
        return $this->showStepIndicator;
    }

    public function showsStepTitles(): bool
    {
        return $this->showStepTitles;
    }

    public function isLinear(): bool
    {
        return $this->linear;
    }

    public function allowsSkip(): bool
    {
        return $this->allowSkip;
    }

    public function requiresCancelConfirmation(): bool
    {
        return $this->confirmCancel;
    }

    public function getBackButtonLabel(): string
    {
        return $this->backButtonLabel;
    }

    public function getNextButtonLabel(): string
    {
        return $this->nextButtonLabel;
    }

    public function getFinishButtonLabel(): string
    {
        return $this->finishButtonLabel;
    }

    public function getCancelButtonLabel(): string
    {
        return $this->cancelButtonLabel;
    }

    public function showsCancel(): bool
    {
        return $this->showCancel;
    }

    // =========================================================================
    // Private Helper Methods
    // =========================================================================

    private function findNextVisibleStepIndex(): ?int
    {
        for ($i = $this->currentStepIndex + 1; $i < count($this->steps); $i++) {
            if (!$this->steps[$i]->isHidden()) {
                return $i;
            }
        }

        return null;
    }

    private function findPreviousVisibleStepIndex(): ?int
    {
        for ($i = $this->currentStepIndex - 1; $i >= 0; $i--) {
            if (!$this->steps[$i]->isHidden()) {
                return $i;
            }
        }

        return null;
    }

    private function getNextStepId(): ?string
    {
        $nextIndex = $this->findNextVisibleStepIndex();

        if ($nextIndex === null) {
            return null;
        }

        return $this->steps[$nextIndex]->getId();
    }

    private function resolveStepIndex(int|string $step): ?int
    {
        if (is_int($step)) {
            if ($step >= 0 && $step < count($this->steps)) {
                return $step;
            }

            return null;
        }

        foreach ($this->steps as $index => $s) {
            if ($s->getId() === $step) {
                return $index;
            }
        }

        return null;
    }
}
