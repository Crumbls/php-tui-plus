<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    BlockWidget::default()
        ->borders(Borders::ALL)
        ->style(
            Style::default()
                ->fg(AnsiColor::Blue)
                ->bg(AnsiColor::Red)
        )
);
