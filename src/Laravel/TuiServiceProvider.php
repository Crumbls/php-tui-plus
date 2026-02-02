<?php

declare(strict_types=1);

namespace Crumbls\Tui\Laravel;

use Crumbls\Tui\Display\Display;
use Crumbls\Tui\DisplayBuilder;
use Illuminate\Support\ServiceProvider;
use PhpTui\Term\Terminal;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class TuiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoopInterface::class, fn () => Loop::get());

        $this->app->singleton(Terminal::class, fn () => Terminal::new());

        $this->app->singleton(Display::class, fn () => DisplayBuilder::default()->build());

        $this->app->singleton(EventBus::class, fn () => new EventBus());

        $this->app->singleton(State::class, fn () => new State());
    }

    public function boot(): void
    {
    }
}
