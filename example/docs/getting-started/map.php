<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\MapResolution;
use Crumbls\Tui\Extension\Core\Shape\MapShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->clear();
$display->draw(
    CanvasWidget::fromIntBounds(-180, 180, -90, 90)
        ->draw(
            MapShape::default()->resolution(MapResolution::High)
        )
);
