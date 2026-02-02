<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Direction;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    GridWidget::default()
        ->direction(Direction::Horizontal)
        ->constraints(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->widgets(
            BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Left')),
            GridWidget::default()
                ->direction(Direction::Vertical)
                ->constraints(
                    Constraint::percentage(50),
                    Constraint::percentage(50),
                )
                ->widgets(
                    BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Top Right')),
                    BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Bottom Right')),
                )
        )
);
