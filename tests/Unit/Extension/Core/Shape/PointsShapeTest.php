<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\PointsShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;

dataset('points', [
    'out of bounds' => [
        PointsShape::new([[100,100],[100,100]], AnsiColor::Red),
        [
            '   ',
            '   ',
            '   ',
        ]
    ],
    'points' => [
        PointsShape::new([[0,0],[1,1],[2,2]], AnsiColor::Red),
        [
            '  •',
            ' • ',
            '•  ',
        ]
    ],
]);

test('points', function (PointsShape $points, array $expected): void {
    $canvas = CanvasWidget::default()
        ->marker(Marker::Dot)
        ->xBounds(AxisBounds::new(0, 2))
        ->yBounds(AxisBounds::new(0, 2))
        ->paint(static function (CanvasContext $context) use ($points): void {
            $context->draw($points);
        });
    $area = Area::fromDimensions(3, 3);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
})->with('points');
