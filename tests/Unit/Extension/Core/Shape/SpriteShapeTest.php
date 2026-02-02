<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\SpriteShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Position\FloatPosition;

dataset('sprites', [
    'block line' => [
        new SpriteShape(
            rows: [
                '█████████████████████████████████',
            ],
            color: AnsiColor::Green,
            alphaChar: ' ',
            xScale: 1,
            yScale: 1,
            position: FloatPosition::at(0, 0)
        ),
        Marker::Block,
        [
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '                                  ',
            '█████████████████████████████████ ',
        ]
    ],
    'identity' => [
        new SpriteShape(
            rows: [
                '       █████',
                '   ████████████████████',
                ' █████████████████████████',
                '█████ ███████████████████████',
                '█████████████████████████████████',
                '█████████████████████████████   ██',
                '███  ████████████████████████',
                '███  ███████████████████████ ',
                '███  ███████████████████████ ',
                '███  ████ ████  ████  ██████ ',
            ],
            color: AnsiColor::Green,
            alphaChar: ' ',
            xScale: 1,
            yScale: 1,
            position: FloatPosition::at(0, 0)
        ),
        Marker::Block,
        [
            '       █████                      ',
            '   ████████████████████           ',
            ' █████████████████████████        ',
            '█████ ███████████████████████     ',
            '█████████████████████████████████ ',
            '█████████████████████████████   ██',
            '███  ████████████████████████     ',
            '███  ███████████████████████      ',
            '███  ███████████████████████      ',
            '███  ████ ████  ████  ██████      ',
        ]
    ],
    'scale to 50%' => [
        new SpriteShape(
            rows: [
                '       █████',
                '   ████████████████████',
                ' █████████████████████████',
                '█████ ███████████████████████',
                '█████████████████████████████████',
                '█████████████████████████████   ██',
                '███  ████████████████████████',
                '███  ███████████████████████ ',
                '███  ███████████████████████ ',
                '███  ████ ████  ████  ██████ ',
            ],
            color: AnsiColor::Green,
            alphaChar: ' ',
            xScale: 0.5,
            yScale: 0.5,
            position: FloatPosition::at(4, 2)
        ),
        Marker::Braille,
        [
            '                                  ',
            '                                  ',
            '                                  ',
            '      ⣤⣤⣿⣿⣧⣤⣤⣤⣤⣤                  ',
            '    ⢠⣿⣿⢻⣿⣿⣿⣿⣿⣿⣿⣿⣿⣧⣤               ',
            '    ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠛⢻⡄            ',
            '    ⢸⣿ ⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡟               ',
            '    ⢸⣿ ⣿⣿⢻⣿⡟⢻⣿⡟⢻⣿⣿⡇               ',
            '                                  ',
            '                                  ',
        ]
    ],
    'scale to 200%' => [
        new SpriteShape(
            rows: [
                '       █████',
                '   ████████████████████',
                ' █████████████████████████',
                '█████ ███████████████████████',
                '█████████████████████████████████',
                '█████████████████████████████   ██',
                '███  ████████████████████████',
                '███  ███████████████████████ ',
                '███  ███████████████████████ ',
                '███  ████ ████  ████  ██████ ',
            ],
            color: AnsiColor::Green,
            alphaChar: ' ',
            xScale: 2,
            yScale: 2,
            position: FloatPosition::at(4, 2)
        ),
        Marker::Braille,
        [
            '    ⢸⣿⣿⣿⣿⣿⡏⠉⠉⠉⢹⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⡇ ⢸⣿⣿⣿⣿⣿⣿⣿⡇ ',
            '    ⢸⣿⣿⣿⣿⣿⡇   ⢸⣿⣿⣿⣿⣿⣿⣿⡇ ⢸⣿⣿⣿⣿⣿⣿⣿⡇ ',
            '                                  ',
            '                                  ',
        ]
    ],
    'change alpha color' => [
        new SpriteShape(
            rows: [
                '       █████                      ',
                '   ████████████████████           ',
                ' █████████████████████████        ',
                '█████ ███████████████████████     ',
                '█████████████████████████████████ ',
                '█████████████████████████████   ██',
                '███  ████████████████████████     ',
                '███  ███████████████████████      ',
                '███  ███████████████████████      ',
                '███  ████ ████  ████  ██████      ',
            ],
            color: AnsiColor::Green,
            alphaChar: '█',
            xScale: 1,
            yScale: 1,
            position: FloatPosition::at(0, 0)
        ),
        Marker::Block,
        [
            '███████     ██████████████████████',
            '███                    ███████████',
            '█                         ████████',
            '     █                       █████',
            '                                 █',
            '                             ███  ',
            '   ██                        █████',
            '   ██                       ██████',
            '   ██                       ██████',
            '   ██    █    ██    ██      ██████'
        ]
    ],
]);

test('sprite', function (SpriteShape $sprite, Marker $marker, array $expected): void {
    $canvas = CanvasWidget::default()
        ->marker($marker)
        ->xBounds(AxisBounds::new(0, 34))
        ->yBounds(AxisBounds::new(0, 10))
        ->paint(static function (CanvasContext $context) use ($sprite): void {
            $context->draw($sprite);
        });
    $area = Area::fromDimensions(34, 10);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toString())->toBe(implode("\n", $expected));
})->with('sprites');
