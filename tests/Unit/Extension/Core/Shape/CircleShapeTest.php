<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\CircleShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;

test('circle', function (): void {
    $circle = CircleShape::fromScalars(5, 2, 5);
    $expected = [
        '     ⢀⣠⢤⣀ ',
        '    ⢰⠋  ⠈⣇',
        '    ⠘⣆⡀ ⣠⠇',
        '      ⠉⠉⠁ ',
        '          ',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Braille)
        ->xBounds(AxisBounds::new(-10, 10))
        ->yBounds(AxisBounds::new(-10, 10))
        ->paint(static function (CanvasContext $context) use ($circle): void {
            $context->draw($circle);
        });
    $area = Area::fromDimensions(10, 5);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
});
