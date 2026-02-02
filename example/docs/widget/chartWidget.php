<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\Chart\Axis;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Extension\Core\Widget\Chart\DataSet;
use Crumbls\Tui\Extension\Core\Widget\Chart\GraphType;
use Crumbls\Tui\Extension\Core\Widget\ChartWidget;
use Crumbls\Tui\Text\Span;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    ChartWidget::new(
        DataSet::new('Ships')
                ->data(
                    [[0, -2], [1, -1], [2, 0], [3, 1], [4, 2], [5, 1], [6, 0], [7, -1]]
                )
                ->marker(Marker::Dot),
        DataSet::new('Birds')
                ->data(
                    [[0, 2], [1, 1], [2, 0], [3, -1], [4, -2], [5, -1], [6, 0], [7, 1]]
                )
                ->graphType(GraphType::Line)
                ->marker(Marker::Braille),
    )
        ->xAxis(
            Axis::default()
                ->labels(
                    Span::fromString('Good'),
                    Span::fromString('Neutral'),
                    Span::fromString('Bad'),
                )
                ->bounds(AxisBounds::new(0, 8))
        )
        ->yAxis(
            Axis::default()
                ->labels(
                    Span::fromString('Profit'),
                    Span::fromString('Neutral'),
                    Span::fromString('Loss')
                )
                ->bounds(AxisBounds::new(-4, 4))
        )
);
