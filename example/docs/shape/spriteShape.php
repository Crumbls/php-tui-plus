<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\SpriteShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Position\FloatPosition;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CanvasWidget::fromIntBounds(0, 30, 0, 15)
        ->marker(Marker::Block)
        ->draw(
            new SpriteShape(
                rows: [
                    ' XXX ',
                    'X   X ',
                    'X   X ',
                    ' XXX ',
                    '  X',
                    'XXXXX',
                    '  X       ', // rows do not need
                    '  X       ', // equals numbers of chars
                    ' X X     ',
                    'X   X    ',
                ],
                color: AnsiColor::White,
                position: FloatPosition::at(2, 2),
            )
        )
);
