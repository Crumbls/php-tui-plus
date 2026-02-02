<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;
use Crumbls\Tui\Style\Style;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    GaugeWidget::default()->ratio(0.25)->style(Style::default()->fg(AnsiColor::Yellow))
);
