<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Direction;

test('grid', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    render($buffer, GridWidget::default()
        ->direction(Direction::Vertical)
        ->constraints(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->widgets(
            BlockWidget::default()->borders(Borders::ALL),
            GridWidget::default()
                ->direction(Direction::Horizontal)
                ->constraints(
                    Constraint::percentage(50),
                    Constraint::percentage(50),
                )
                ->widgets(
                    BlockWidget::default()->borders(Borders::ALL),
                    BlockWidget::default()->borders(Borders::ALL),
                )
        ));

    expect($buffer->toLines())->toEqual([
        '┌────────┐',
        '│        │',
        '│        │',
        '│        │',
        '└────────┘',
        '┌───┐┌───┐',
        '│   ││   │',
        '│   ││   │',
        '│   ││   │',
        '└───┘└───┘',
    ]);
});

test('not enough constraints', function (): void {
    $area = Area::fromDimensions(10, 10);
    $buffer = Buffer::empty($area);
    $grid = GridWidget::default()
        ->widgets(
            ParagraphWidget::fromText(Text::fromString('Hello World'))
        );
    render($buffer, $grid);
})->throws(RuntimeException::class, 'Widget at offset 0 has no corresponding constraint. Ensure that the number of constraints match or exceed the number of widgets');
