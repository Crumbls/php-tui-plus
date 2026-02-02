<?php

declare(strict_types=1);

namespace Crumbls\Tui\Laravel;

use Closure;
use Crumbls\Tui\Display\Display;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Widget\Widget;
use Illuminate\Console\Command;
use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\KeyCode;
use PhpTui\Term\Terminal;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;

abstract class TuiCommand extends Command
{
    protected LoopInterface $loop;

    protected Terminal $terminal;

    protected Display $display;

    protected EventBus $eventBus;

    protected EventParser $eventParser;

    protected bool $running = false;

    protected bool $typingMode = false;

    protected int $fps = 60;

    protected bool $mouseEnabled = true;

    protected bool $alternateScreen = true;

    public function handle(): int
    {
        $this->bootstrap();

        try {
            $this->start();
        } finally {
            $this->shutdown();
        }

        return 0;
    }

    protected function bootstrap(): void
    {
        $this->loop = Loop::get();
        $this->terminal = Terminal::new();
        $this->display = DisplayBuilder::default()->build();
        $this->eventBus = new EventBus();
        $this->eventParser = new EventParser();
    }

    protected function start(): void
    {
        $this->running = true;

        if ($this->alternateScreen) {
            $this->terminal->execute(Actions::alternateScreenEnable());
        }

        $this->terminal->execute(Actions::cursorHide());

        if ($this->mouseEnabled) {
            $this->terminal->execute(Actions::enableMouseCapture());
        }

        $this->terminal->enableRawMode();
        $this->terminal->execute(Actions::moveCursor(0, 0));
        $this->display->clear();

        $this->init();
        $this->startRenderLoop();
        $this->startInputLoop();

        $this->loop->addSignal(SIGINT, fn () => $this->quit());
        $this->loop->addSignal(SIGTERM, fn () => $this->quit());

        $this->loop->run();
    }

    protected function startRenderLoop(): void
    {
        $this->loop->addPeriodicTimer(1 / $this->fps, function (): void {
            if (!$this->running) {
                return;
            }

            $area = $this->display->viewportArea();
            $widget = $this->render($area);

            if ($widget !== null) {
                $this->display->draw($widget);
            }
        });
    }

    protected function startInputLoop(): void
    {
        $this->eventBus->on('typing_mode', fn (array $data) => $this->typingMode = $data['state'] ?? false);

        $stdin = new ReadableResourceStream(STDIN, $this->loop);

        $stdin->on('data', function (string $data): void {
            $this->eventParser->advance($data, false);

            foreach ($this->eventParser->drain() as $event) {
                if (!in_array($event::class, [CharKeyEvent::class, CodedKeyEvent::class, MouseEvent::class], true)) {
                    continue;
                }

                if ($event instanceof MouseEvent && $this->typingMode) {
                    continue;
                }

                if ($this->typingMode && $event instanceof CharKeyEvent) {
                    $this->eventBus->emit('input', ['char' => $event->char, 'raw' => $data]);

                    continue;
                }

                if ($this->typingMode && $event instanceof CodedKeyEvent && $event->code === KeyCode::Esc) {
                    $this->eventBus->emit('typing_mode', ['state' => false]);

                    continue;
                }

                $this->handleEvent($event);
                $this->eventBus->emit($event::class, ['event' => $event]);
            }
        });
    }

    protected function shutdown(): void
    {
        $this->running = false;
        $this->loop->stop();
        $this->terminal->disableRawMode();

        if ($this->mouseEnabled) {
            $this->terminal->execute(Actions::disableMouseCapture());
        }

        $this->terminal->execute(Actions::cursorShow());

        if ($this->alternateScreen) {
            $this->terminal->execute(Actions::alternateScreenDisable());
        }
    }

    public function quit(): void
    {
        $this->running = false;
        $this->loop->stop();
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    public function getTerminal(): Terminal
    {
        return $this->terminal;
    }

    public function getDisplay(): Display
    {
        return $this->display;
    }

    public function getEventBus(): EventBus
    {
        return $this->eventBus;
    }

    public function enableTypingMode(): void
    {
        $this->typingMode = true;
        $this->eventBus->emit('typing_mode', ['state' => true]);
    }

    public function disableTypingMode(): void
    {
        $this->typingMode = false;
        $this->eventBus->emit('typing_mode', ['state' => false]);
    }

    protected function after(float $seconds, Closure $callback): void
    {
        $this->loop->addTimer($seconds, $callback);
    }

    protected function every(float $seconds, Closure $callback): void
    {
        $this->loop->addPeriodicTimer($seconds, $callback);
    }

    /**
     * Initialize the TUI application.
     * Called after bootstrap but before the render loop starts.
     */
    abstract protected function init(): void;

    /**
     * Render the main widget.
     * Called on every frame at the configured FPS.
     */
    abstract protected function render(\Crumbls\Tui\Display\Area $area): ?Widget;

    /**
     * Handle keyboard/mouse events.
     * Override this to handle input in your command.
     */
    protected function handleEvent(CharKeyEvent|CodedKeyEvent|MouseEvent $event): void
    {
        if ($event instanceof CodedKeyEvent && $event->code === KeyCode::Esc) {
            $this->quit();
        }

        if ($event instanceof CharKeyEvent && $event->char === 'q') {
            $this->quit();
        }
    }
}
