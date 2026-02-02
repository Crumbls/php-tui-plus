<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\Buffer\BufferContext;
use Crumbls\Tui\Extension\Core\Widget\BufferWidget;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    BufferWidget::new(function (BufferContext $context): void {
        $context->draw(BlockWidget::default()->borders(Borders::ALL));
        $context->buffer->putString(Position::at(10, 10), 'Hello World');
    })
);
