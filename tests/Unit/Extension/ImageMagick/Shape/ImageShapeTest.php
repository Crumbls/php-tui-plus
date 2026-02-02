<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\CanvasRenderer;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImagePainter;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImageShape;
use Crumbls\Tui\Position\FloatPosition;
use Crumbls\Tui\Widget\WidgetRenderer\NullWidgetRenderer;

test('renders image (no colors in this test!)', function (): void {
    if (!extension_loaded('imagick')) {
        $this->markTestSkipped('Image Magick extension not installed');
    }

    $image = ImageShape::fromPath(__DIR__ . '/example.jpg');
    $expected = [
        '██████████',
        '██████████',
        '██████████',
        '██████████',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Block)
        ->xBounds(AxisBounds::new(0, 10))
        ->yBounds(AxisBounds::new(0, 4))
        ->paint(static function (CanvasContext $context) use ($image): void {
            $context->draw($image);
        });
    $area = Area::fromDimensions(10, 4);
    $buffer = Buffer::empty($area);
    (new CanvasRenderer(
        new ImagePainter(),
    ))->render(new NullWidgetRenderer(), $canvas, $buffer, $buffer->area());
    expect($buffer->toString())->toBe(implode("\n", $expected));
});

test('position image', function (): void {
    if (!extension_loaded('imagick')) {
        $this->markTestSkipped('Image Magick extension not installed');
    }

    $image = ImageShape::fromPath(__DIR__ . '/example.jpg')->position(FloatPosition::at(3, 2));
    $expected = [
        '  ████████',
        '  ████████',
        '          ',
        '          ',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Block)
        ->xBounds(AxisBounds::new(0, 10))
        ->yBounds(AxisBounds::new(0, 4))
        ->paint(static function (CanvasContext $context) use ($image): void {
            $context->draw($image);
        });
    $area = Area::fromDimensions(10, 4);
    $buffer = Buffer::empty($area);
    (new CanvasRenderer(
        new ImagePainter(),
    ))->render(new NullWidgetRenderer(), $canvas, $buffer, $buffer->area());
    expect($buffer->toString())->toBe(implode("\n", $expected));
});
