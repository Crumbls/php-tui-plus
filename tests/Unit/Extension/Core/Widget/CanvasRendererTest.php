<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Display\Cell;
use Crumbls\Tui\Extension\Core\Shape\CircleShape;
use Crumbls\Tui\Extension\Core\Shape\LineShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Text\Line as DTLLine;

test('from int bounds', function (): void {
    $canvas = CanvasWidget::fromIntBounds(1, 320, 2, 240);

    expect($canvas->xBounds)->toEqual(AxisBounds::new(1, 320));
    expect($canvas->yBounds)->toEqual(AxisBounds::new(2, 240));
});

test('draw', function (): void {
    $area = Area::fromDimensions(10, 10);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));

    $canvas = CanvasWidget::fromIntBounds(0, 10, 0, 10);
    $canvas->draw(CircleShape::fromScalars(5, 5, 5)->color(AnsiColor::Green));
    render($buffer, $canvas);
    $expected = [
        'x⢀⡴⠋⠉⠉⠳⣄xx',
        '⢀⡞xxxxx⠘⣆x',
        '⡼xxxxxxx⠸⡄',
        '⡇xxxxxxxx⡇',
        '⡇xxxxxxxx⣇',
        '⡇xxxxxxxx⡇',
        '⡇xxxxxxxx⡇',
        '⢹⡀xxxxxx⣸⠁',
        'x⢳⡀xxxx⣰⠃x',
        'xx⠙⠦⢤⠤⠞⠁xx',
    ];

    expect($buffer->toLines())->toEqual($expected);

    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual($expected);
});

test('draw multiple', function (): void {
    $area = Area::fromDimensions(5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));

    $canvas = CanvasWidget::fromIntBounds(0, 5, 0, 5);
    $canvas->draw(
        CircleShape::fromScalars(1, 1, 1),
        CircleShape::fromScalars(4, 4, 1),
    );
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        'xx⢸⠉⣇',
        'xx⠸⣄⡇',
        '⣀⡀xxx',
        '⡇⢹xxx',
        '⢧⠼xxx',
    ]);
});

test('render marker bar', function (): void {
    $horizontalLine = LineShape::fromScalars(0.0, 0.0, 10.0, 0.0)->color(AnsiColor::Green);
    $verticalLine = LineShape::fromScalars(0.0, 0.0, 0.0, 10.0)->color(AnsiColor::Green);
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context) use ($horizontalLine, $verticalLine): void {
            $context->draw($verticalLine);
            $context->draw($horizontalLine);
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 10.0))->marker(Marker::Bar);
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '▄xxxx',
        '▄xxxx',
        '▄xxxx',
        '▄xxxx',
        '▄▄▄▄▄',
    ]);
});

test('render marker block', function (): void {
    $horizontalLine = LineShape::fromScalars(0.0, 0.0, 10.0, 0.0)->color(AnsiColor::Green);
    $verticalLine = LineShape::fromScalars(0.0, 0.0, 0.0, 10.0)->color(AnsiColor::Green);
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context) use ($horizontalLine, $verticalLine): void {
            $context->draw($verticalLine);
            $context->draw($horizontalLine);
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 10.0))->marker(Marker::Block);
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '█xxxx',
        '█xxxx',
        '█xxxx',
        '█xxxx',
        '█████',
    ]);
});

test('render marker dot', function (): void {
    $horizontalLine = LineShape::fromScalars(0.0, 0.0, 10.0, 0.0)->color(AnsiColor::Green);
    $verticalLine = LineShape::fromScalars(0.0, 0.0, 0.0, 10.0)->color(AnsiColor::Green);
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context) use ($horizontalLine, $verticalLine): void {
            $context->draw($verticalLine);
            $context->draw($horizontalLine);
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 10.0))->marker(Marker::Dot);
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '•xxxx',
        '•xxxx',
        '•xxxx',
        '•xxxx',
        '•••••',
    ]);
});

test('render marker braille', function (): void {
    $horizontalLine = LineShape::fromScalars(0.0, 0.0, 10.0, 0.0)->color(AnsiColor::Green);
    $verticalLine = LineShape::fromScalars(0.0, 0.0, 0.0, 10.0)->color(AnsiColor::Green);
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context) use ($horizontalLine, $verticalLine): void {
            $context->draw($verticalLine);
            $context->draw($horizontalLine);
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 10.0))->marker(Marker::Braille);
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '⡇xxxx',
        '⡇xxxx',
        '⡇xxxx',
        '⡇xxxx',
        '⣇⣀⣀⣀⣀',
    ]);
});

test('render marker half-block', function (): void {
    $horizontalLine = LineShape::fromScalars(0.0, 0.0, 10.0, 0.0)->color(AnsiColor::Green);
    $verticalLine = LineShape::fromScalars(0.0, 0.0, 0.0, 10.0)->color(AnsiColor::Green);
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context) use ($horizontalLine, $verticalLine): void {
            $context->draw($verticalLine);
            $context->draw($horizontalLine);
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 10.0))->marker(Marker::HalfBlock);
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::filled($area, Cell::fromChar('x'));
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '█xxxx',
        '█xxxx',
        '█xxxx',
        '█xxxx',
        '█▄▄▄▄',
    ]);
});

test('labels', function (): void {
    $canvas = CanvasWidget::default()->paint(
        static function (CanvasContext $context): void {
            $context->print(0, 0, DTLLine::fromString('Hello'));
        }
    )->xBounds(AxisBounds::new(0.0, 10.0))->yBounds(AxisBounds::new(0.0, 5));
    $area = Area::fromScalars(0, 0, 5, 5);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);

    expect($buffer->toLines())->toEqual([
        '     ',
        '     ',
        '     ',
        '     ',
        'Hello',
    ]);
});
