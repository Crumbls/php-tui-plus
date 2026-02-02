<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Bdf\FontRegistry;
use Crumbls\Tui\Extension\Bdf\Shape\TextRenderer;
use Crumbls\Tui\Extension\Bdf\Shape\TextShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasRenderer;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Position\FloatPosition;
use Crumbls\Tui\Widget\WidgetRenderer\NullWidgetRenderer;

dataset('textShapes', [
    'text' => [
        new TextShape(
            font: 'default',
            text: 'Hello World',
            color: AnsiColor::Green,
            position: FloatPosition::at(0, 0),
        ),
        [
            '█   █        ██    ██               █   █              ██       █',
            '█   █  ███    █     █    ███        █   █  ███  █ ██    █    ██ █',
            '█████ █   █   █     █   █   █       █ █ █ █   █ ██  █   █   █  ██',
            '█   █ █████   █     █   █   █       █ █ █ █   █ █       █   █   █',
            '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
            '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
        ]
    ],
    'scale x' => [
        new TextShape(
            font: 'default',
            scaleX: 2,
            text: 'Hello',
            color: AnsiColor::Green,
            position: FloatPosition::at(0, 0),
        ),
        [
            '██      ██                ████        ████                       ',
            '██      ██    ██████        ██          ██        ██████         ',
            '██████████  ██      ██      ██          ██      ██      ██       ',
            '██      ██  ██████████      ██          ██      ██      ██       ',
            '██      ██  ██              ██          ██      ██      ██       ',
            '██      ██    ██████      ██████      ██████      ██████         ',
        ]
    ],
    'scale y' => [
        new TextShape(
            font: 'default',
            scaleY: 2,
            text: 'Hello World',
            color: AnsiColor::Green,
            position: FloatPosition::at(0, 0),
        ),
        [
            '█████ █████   █     █   █   █       █ █ █ █   █ ██  █   █   █  ██',
            '█   █ █████   █     █   █   █       █ █ █ █   █ █       █   █   █',
            '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
            '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
            '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
            '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
        ]
    ],
]);

test('text shape', function (TextShape $text, array $expected): void {
    $canvas = CanvasWidget::fromIntBounds(0, 65, 0, 6)
        ->marker(Marker::Block)
        ->paint(static function (CanvasContext $context) use ($text): void {
            $context->draw($text);
        });
    $area = Area::fromDimensions(65, 6);
    $buffer = Buffer::empty($area);
    (new CanvasRenderer(new TextRenderer(FontRegistry::default())))->render(new NullWidgetRenderer(), $canvas, $buffer, $buffer->area());
    expect($buffer->toLines())->toBe($expected);
})->with('textShapes');

dataset('scales', [
    'canvas more narrow than area' => [
        Area::fromDimensions(12, 6),
        6,
        6,
        new TextShape(
            font: 'default',
            text: 'O',
            color: AnsiColor::Green,
            position: FloatPosition::at(0, 0),
        ),
        [
            ' ██████████ ',
            ' ██      ██ ',
            ' ██      ██ ',
            ' ██      ██ ',
            ' ██      ██ ',
            '   ██████   ',
        ]
    ],
    'canvas more short than area' => [
        Area::fromDimensions(6, 12),
        6,
        6,
        new TextShape(
            font: 'default',
            text: 'O',
            color: AnsiColor::Green,
            position: FloatPosition::at(0, 0),
        ),
        [
            '█████ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            '█   █ ',
            ' ███  ',
            ' ███  ',
        ]
    ],
]);

test('scale', function (Area $area, int $boundsX, int $boundsY, TextShape $text, array $expected): void {
    $canvas = CanvasWidget::fromIntBounds(0, $boundsX, 0, $boundsY)
        ->marker(Marker::Block)
        ->paint(static function (CanvasContext $context) use ($text): void {
            $context->draw($text);
        });
    $buffer = Buffer::empty($area);
    (new CanvasRenderer(
        new TextRenderer(FontRegistry::default())
    ))->render(
        new NullWidgetRenderer(),
        $canvas,
        $buffer,
        $buffer->area(),
    );
    expect($buffer->toLines())->toBe($expected);
})->with('scales');
