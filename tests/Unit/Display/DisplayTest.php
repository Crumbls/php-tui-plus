<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Backend\DummyBackend;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\PointsShape;
use Crumbls\Tui\Extension\Core\Widget\Buffer\BufferContext;
use Crumbls\Tui\Extension\Core\Widget\BufferWidget;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Position\Position;

test('autoresize', function (): void {
    $backend = DummyBackend::fromDimensions(4, 4);
    $terminal = DisplayBuilder::default($backend)->build();
    $backend->setDimensions(2, 2);

    // intentionally go out of bounds
    $terminal->draw(new BufferWidget(static function (BufferContext $context): void {
        for ($y = 0; $y < 4; $y++) {
            for ($x = 0; $x < 4; $x++) {
                $context->buffer->putString(new Position($x, $y), 'h');
            }
        }
    }));

    expect($backend->toString())->toEqual("hh  \nhh  \n    \n    ");
});

test('draw', function (): void {
    $backend = DummyBackend::fromDimensions(4, 4);
    $terminal = DisplayBuilder::default($backend)->build();
    $terminal->draw(new BufferWidget(static function (BufferContext $context): void {
        $x = 0;
        for ($y = 0; $y <= 4; $y++) {
            $context->buffer->putString(new Position($x++, $y), 'x');
        }
    }));

    expect($backend->flushed())->toEqual("x   \n x  \n  x \n   x");
});

test('render', function (): void {
    $backend = DummyBackend::fromDimensions(4, 4);
    $terminal = DisplayBuilder::default($backend)->build();
    $terminal->draw(CanvasWidget::fromIntBounds(0, 3, 0, 3)->marker(Marker::Dot)->draw(PointsShape::new([
        [3, 3], [2, 2], [1, 1], [0, 0]
    ], AnsiColor::Green)));

    expect($backend->flushed())->toEqual("   •\n  • \n •  \n•   ");
});

test('flushes', function (): void {
    $backend = DummyBackend::fromDimensions(10, 4);
    $terminal = DisplayBuilder::default($backend)->build();
    $terminal->buffer()->putString(new Position(2, 1), 'X');
    $terminal->buffer()->putString(new Position(0, 0), 'X');
    $terminal->flush();

    expect($backend->toString())->toEqual(implode("\n", [
        'X         ',
        '  X       ',
        '          ',
        '          ',
    ]));
});

test('inline viewport', function (): void {
    $backend = new DummyBackend(10, 10, Position::at(0, 15));
    $terminal = DisplayBuilder::default($backend)->inline(10)->build();
    $terminal->draw(ParagraphWidget::fromString('Hello'));

    expect($terminal->viewportArea()->top())->toEqual(6);
    expect($terminal->viewportArea()->left())->toEqual(0);
});

test('fixed viewport', function (): void {
    $backend = new DummyBackend(10, 10, Position::at(0, 15));
    $terminal = DisplayBuilder::default($backend)->fixed(1, 2, 20, 15)->build();
    $terminal->draw(ParagraphWidget::fromString('Hello'));

    expect($terminal->viewportArea()->position->x)->toEqual(1);
    expect($terminal->viewportArea()->position->y)->toEqual(2);
    expect($terminal->viewportArea()->top())->toEqual(2);
    expect($terminal->viewportArea()->left())->toEqual(1);
    expect($terminal->viewportArea()->right())->toEqual(21);
    expect($terminal->viewportArea()->bottom())->toEqual(17);
});

test('insert before', function (): void {
    $backend = new DummyBackend(15, 10, Position::at(0, 0));
    $terminal = DisplayBuilder::default($backend)->inline(2)->build();
    $terminal->insertBefore(2, ParagraphWidget::fromString(
        <<<'EOT'
            Before
            World
            EOT
    ));
    $terminal->draw(ParagraphWidget::fromString('Hello World'));

    expect($backend->toString())->toEqual(implode("\n", [
        'Before         ',
        'World          ',
        'Hello World    ',
        '               ',
        '               ',
        '               ',
        '               ',
        '               ',
        '               ',
        '               ',
    ]));
});
