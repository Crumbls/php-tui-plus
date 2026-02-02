<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\CircleShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(-1, 21, -1, 21)
        ->marker(Marker::Dot)
        ->draw(CircleShape::fromScalars(10, 10, 10)->color(AnsiColor::Green))
);
