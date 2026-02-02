<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->fixed(2, 2, 4, 4)->build();
$display->draw(BlockWidget::default()->borders(Borders::ALL));
