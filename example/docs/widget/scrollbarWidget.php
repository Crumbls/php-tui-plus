<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    ScrollbarWidget::default()
        ->state(new ScrollbarState(30, 15, 5))
);
