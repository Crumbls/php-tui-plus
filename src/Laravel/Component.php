<?php

declare(strict_types=1);

namespace Crumbls\Tui\Laravel;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Widget\Widget;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

abstract class Component
{
    protected LoopInterface $loop;

    protected EventBus $eventBus;

    protected bool $active = false;

    protected bool $focused = false;

    /** @var array<int, TimerInterface> */
    protected array $timers = [];

    /** @var array<string, Component> */
    protected array $children = [];

    public function __construct(LoopInterface $loop, EventBus $eventBus)
    {
        $this->loop = $loop;
        $this->eventBus = $eventBus;

        $this->registerEventListeners();
        $this->init();
    }

    protected function registerEventListeners(): void
    {
        $this->eventBus->on(CharKeyEvent::class, function (array $data): void {
            if ($this->active || $this->focused) {
                $this->onKeyPress($data['event']);
            }
        });

        $this->eventBus->on(CodedKeyEvent::class, function (array $data): void {
            if ($this->active || $this->focused) {
                $this->onKeyPress($data['event']);
            }
        });

        $this->eventBus->on(MouseEvent::class, function (array $data): void {
            if ($this->active || $this->focused) {
                $this->onMouse($data['event']);
            }
        });
    }

    /**
     * Initialize the component.
     * Override to set up state, add timers, etc.
     */
    protected function init(): void
    {
    }

    /**
     * Add a child component.
     */
    protected function addChild(string $name, Component $component): self
    {
        $this->children[$name] = $component;

        return $this;
    }

    /**
     * Get a child component.
     */
    protected function getChild(string $name): ?Component
    {
        return $this->children[$name] ?? null;
    }

    /**
     * Render a child component.
     */
    protected function renderChild(string $name, Area $area): ?Widget
    {
        $child = $this->getChild($name);

        return $child?->render($area);
    }

    /**
     * Set up a periodic timer.
     */
    protected function every(float $seconds, callable $callback): TimerInterface
    {
        $timer = $this->loop->addPeriodicTimer($seconds, $callback);
        $this->timers[] = $timer;

        return $timer;
    }

    /**
     * Set up a one-time timer.
     */
    protected function after(float $seconds, callable $callback): TimerInterface
    {
        return $this->loop->addTimer($seconds, $callback);
    }

    /**
     * Cancel a timer.
     */
    protected function cancelTimer(TimerInterface $timer): void
    {
        $this->loop->cancelTimer($timer);
        $this->timers = array_filter($this->timers, fn ($t) => $t !== $timer);
    }

    /**
     * Cancel all timers.
     */
    protected function cancelAllTimers(): void
    {
        foreach ($this->timers as $timer) {
            $this->loop->cancelTimer($timer);
        }

        $this->timers = [];
    }

    /**
     * Emit an event.
     *
     * @param array<string, mixed> $data
     */
    protected function emit(string $event, array $data = []): void
    {
        $this->eventBus->emit($event, $data);
    }

    /**
     * Listen for an event.
     */
    protected function on(string $event, callable $callback): void
    {
        $this->eventBus->on($event, $callback);
    }

    public function activate(): self
    {
        $this->active = true;

        return $this;
    }

    public function deactivate(): self
    {
        $this->active = false;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function focus(): self
    {
        $this->focused = true;

        return $this;
    }

    public function blur(): self
    {
        $this->focused = false;

        return $this;
    }

    public function isFocused(): bool
    {
        return $this->focused;
    }

    /**
     * Handle key press events.
     * Override to handle keyboard input.
     */
    protected function onKeyPress(CharKeyEvent|CodedKeyEvent $event): void
    {
    }

    /**
     * Handle mouse events.
     * Override to handle mouse input.
     */
    protected function onMouse(MouseEvent $event): void
    {
    }

    /**
     * Render the component.
     */
    abstract public function render(Area $area): Widget;
}
