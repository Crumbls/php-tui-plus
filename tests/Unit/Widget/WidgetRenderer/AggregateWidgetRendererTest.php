<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;
use Crumbls\Tui\Widget\WidgetRenderer\AggregateWidgetRenderer;
use Crumbls\Tui\Widget\WidgetRenderer\NullWidgetRenderer;

test('self rendering widget', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    (new AggregateWidgetRenderer([]))->render(
        new NullWidgetRenderer(),
        new class implements WidgetRenderer, Widget {
            public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
            {
                $buffer->putLine(Position::at(0, 0), Line::fromString('Hello'), 5);
            }
        },
        $buffer,
        Area::fromDimensions(10, 10)
    );
    expect($buffer->toString())->toContain('Hello');
});
