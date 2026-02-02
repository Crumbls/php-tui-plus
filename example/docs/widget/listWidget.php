<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\List\ListItem;
use Crumbls\Tui\Extension\Core\Widget\List\ListState;
use Crumbls\Tui\Extension\Core\Widget\ListWidget;
use Crumbls\Tui\Text\Text;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    ListWidget::default()
        ->highlightSymbol('😼')
        ->state(new ListState(0, 2))
        ->items(
            ListItem::new(Text::fromString('Item one')),
            ListItem::new(Text::fromString('Item two')),
            ListItem::new(Text::fromString('Item three')),
            ListItem::new(Text::fromString('Item four')),
        )
);
