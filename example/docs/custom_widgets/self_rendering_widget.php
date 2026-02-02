<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

require 'vendor/autoload.php';

final class MyCustomWidget implements Widget, WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        $buffer->putString(Position::at(0, 0), 'Hello World!');
    }
}

$display = DisplayBuilder::default()->build();
$display->draw(new MyCustomWidget());
