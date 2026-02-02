<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Backend\DummyBackend;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Display\DisplayExtension;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\ClosurePainter;
use Crumbls\Tui\Extension\Core\Shape\ClosureShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\ClosureRenderer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

test('build default', function (): void {
    $dummy = new DummyBackend(10, 10);
    $display = DisplayBuilder::default($dummy)->build();
    expect(true)->toBeTrue();
});

test('add extension', function (): void {
    $widgetRendered = false;
    $shapePainted = false;
    $extension = new class($widgetRendered, $shapePainted) implements DisplayExtension {
        public function __construct(private mixed &$widgetRendered, private mixed &$shapePainted)
        {
        }
        public function widgetRenderers(): array
        {
            return [new ClosureRenderer(
                function (WidgetRenderer $renderer, Widget $widget, Buffer $buffer): void {
                    $this->widgetRendered = true;
                }
            )];
        }
        public function shapePainters(): array
        {
            return [new ClosurePainter()];
        }
    };

    $dummy = new DummyBackend(10, 10);
    $display = DisplayBuilder::default($dummy)
        ->addExtension($extension)
        ->fullscreen()
        ->build();
    $display->draw(
        CanvasWidget::default()->draw(new ClosureShape(static function () use (&$shapePainted): void {
            $shapePainted = true;
        }))
    );

    expect($widgetRendered)->toBeTrue();
    expect($shapePainted)->toBeTrue();
});
