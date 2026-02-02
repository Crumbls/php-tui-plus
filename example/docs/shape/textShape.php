<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Bdf\BdfExtension;
use Crumbls\Tui\Extension\Bdf\FontRegistry;
use Crumbls\Tui\Extension\Bdf\Shape\TextShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Position\FloatPosition;

require 'vendor/autoload.php';

// create the font registry
// this is EXPENSIVE to create, only do it once!
$registry = FontRegistry::default();

$display = DisplayBuilder::default()
    ->addExtension(new BdfExtension())
    ->build();

$display->draw(
    CanvasWidget::fromIntBounds(0, 50, 0, 20)
        ->marker(Marker::Block)
        ->draw(
            new TextShape(
                font: 'default',
                text: 'Hello!',
                color: AnsiColor::Green,
                position: FloatPosition::at(10, 7),
            ),
        )
);
