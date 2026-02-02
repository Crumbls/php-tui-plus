<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\CompositeWidget;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarWidget;
use Crumbls\Tui\Widget\Borders;

test('composite', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, CompositeWidget::fromWidgets(
        BlockWidget::default()->borders(Borders::ALL),
        ScrollbarWidget::default()->state(new ScrollbarState(20, 5, 5)),
    ));

    expect($buffer->toLines())->toEqual([
        '▲───┐',
        '█   │',
        '║   │',
        '║   │',
        '▼───┘',
    ]);
});

test('no widgets', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, CompositeWidget::fromWidgets());

    expect($buffer->toLines())->toEqual([
        '     ',
        '     ',
        '     ',
        '     ',
        '     ',
    ]);
});
