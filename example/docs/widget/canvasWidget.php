<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\CircleShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(-1, 21, -1, 21)
        // the marker determines both the effective resolution of
        // the canvas and the "mark" that is made
        ->marker(Marker::Dot)

        // note can use `$canvas->draw($shape, ...)` without the closure for
        // most cases
        ->paint(function (CanvasContext $context): void {

            $context->draw(CircleShape::fromScalars(10, 10, 10)->color(AnsiColor::Green));
        })
);
