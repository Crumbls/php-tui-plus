<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Canvas\Painter;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\ClosureShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Text\Line;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(-1, 21, -1, 21)
        ->marker(Marker::Dot)
        ->draw(
            new ClosureShape(
                function (Painter $painter): void {
                    $painter->context->print(
                        0,
                        0,
                        Line::fromString('Hello World')
                    );
                }
            )
        )
);
