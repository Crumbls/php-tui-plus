<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\LineShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(0, 20, 0, 20)
        ->marker(Marker::Dot)
        ->draw(LineShape::fromScalars(
            0,  // x1
            0,  // y1
            20, // x2
            20, // y2
        )->color(AnsiColor::Green))
);
