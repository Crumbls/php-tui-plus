<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\CompositeWidget;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarOrientation;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarWidget;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    CompositeWidget::fromWidgets(
        BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Window 1')),
        ScrollbarWidget::default()->state(new ScrollbarState(20, 5, 5)),
        ScrollbarWidget::default()
            ->state(new ScrollbarState(20, 5, 5))
            ->orientation(ScrollbarOrientation::VerticalRight),
    )
);
