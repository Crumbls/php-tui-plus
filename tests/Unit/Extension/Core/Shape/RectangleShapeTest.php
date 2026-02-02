<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\RectangleShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;

test('rectangle', function (): void {
    $rectangle = RectangleShape::fromScalars(0, 0, 10, 10)->color(AnsiColor::Reset);
    $expected = [
        '██████████',
        '█        █',
        '█        █',
        '█        █',
        '█        █',
        '█        █',
        '█        █',
        '█        █',
        '█        █',
        '██████████',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Block)
        ->xBounds(AxisBounds::new(0, 10))
        ->yBounds(AxisBounds::new(0, 10))
        ->paint(static function (CanvasContext $context) use ($rectangle): void {
            $context->draw($rectangle);
        });
    $area = Area::fromDimensions(10, 10);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
});
