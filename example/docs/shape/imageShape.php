<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\ImageMagick\ImageMagickExtension;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImageShape;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()
    ->addExtension(new ImageMagickExtension())
    ->build();
$display->draw(
    CanvasWidget::fromIntBounds(0, 320, 0, 240)
        ->marker(Marker::HalfBlock)
        ->draw(
            ImageShape::fromPath(__DIR__ . '/example.jpg')
        )
);
