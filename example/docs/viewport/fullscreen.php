<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

// fullscreen is the default so it can be omitted
$display = DisplayBuilder::default()->fullscreen()->build();
$display->draw(BlockWidget::default()->borders(Borders::ALL));
