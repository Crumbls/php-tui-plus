<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\MapResolution;
use Crumbls\Tui\Extension\Core\Shape\MapShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(-180, 180, -90, 90)
        ->marker(Marker::Braille)
        ->draw(
            MapShape::default()
                ->resolution(MapResolution::High)
                ->color(AnsiColor::Green)
        )
);
