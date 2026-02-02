<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\TabsWidget;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Span;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    TabsWidget::default()
        ->titles(
            Line::fromString('Tab 0'),
            Line::fromString('Tab 1'),
            Line::fromString('Tab 3'),
        )
        ->select(0)
        ->highlightStyle(Style::default()->white()->onRed())
        ->divider(Span::fromString('|'))
);
