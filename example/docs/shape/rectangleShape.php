<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\RectangleShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(0, 10, 0, 10)
        ->marker(Marker::Dot)
        ->draw(
            RectangleShape::fromScalars(
                0,
                0,
                10,
                10,
            )->color(
                AnsiColor::Green
            )
        )
);
