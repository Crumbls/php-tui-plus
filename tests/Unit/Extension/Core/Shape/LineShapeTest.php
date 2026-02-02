<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\LineShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;

dataset('lines', [
    'out of bounds' => [
        LineShape::fromScalars(-1.0, -1.0, 10.0, 10.0),
        [
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
        ]
    ],
    'horizontal' => [
        LineShape::fromScalars(0.0, 0.0, 10.0, 0.0),
        [
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '••••••••••',
        ]
    ],
    'horizontal 2' => [
        LineShape::fromScalars(10.0, 10.0, 0.0, 10.0),
        [
            '••••••••••',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
            '          ',
        ]
    ],
    'vertical' => [
        LineShape::fromScalars(0.0, 0.0, 0.0, 10.0),
        [
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
            '•         ',
        ]
    ],
    'diagonal' => [
        LineShape::fromScalars(0.0, 0.0, 10.0, 5.0),
        [
            '          ',
            '          ',
            '          ',
            '          ',
            '         •',
            '       •• ',
            '     ••   ',
            '   ••     ',
            ' ••       ',
            '•         ',
        ]
    ],
    'diagonal dy > dx, y1 < y2' => [
        LineShape::fromScalars(0.0, 0.0, 5.0, 10.0),
        [
            '    •     ',
            '    •     ',
            '   •      ',
            '   •      ',
            '  •       ',
            '  •       ',
            ' •        ',
            ' •        ',
            '•         ',
            '•         ',
        ]
    ],
    'diagonal dy < dx, x1 < x2' => [
        LineShape::fromScalars(10.0, 0.0, 0.0, 5.0),
        [
            '          ',
            '          ',
            '          ',
            '          ',
            '•         ',
            ' ••       ',
            '   ••     ',
            '     ••   ',
            '       •• ',
            '         •',
        ]
    ],
    'diagonal dy > dx, y1 > y2' => [
        LineShape::fromScalars(0.0, 10.0, 5.0, 0.0),
        [
            '•         ',
            '•         ',
            ' •        ',
            ' •        ',
            '  •       ',
            '  •       ',
            '   •      ',
            '   •      ',
            '    •     ',
            '    •     ',
        ]
    ],
]);

test('line', function (LineShape $line, array $expected): void {
    $canvas = CanvasWidget::default()
        ->marker(Marker::Dot)
        ->xBounds(AxisBounds::new(0, 10))
        ->yBounds(AxisBounds::new(0, 10))
        ->paint(static function (CanvasContext $context) use ($line): void {
            $context->draw($line);
        });
    $area = Area::fromDimensions(10, 10);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
})->with('lines');
