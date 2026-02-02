<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Grid\CharGrid;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Position\Position;

test('zero size', function (): void {
    $grid = CharGrid::new(0, 0, 'X');
    $grid->paint(Position::at(1, 1), AnsiColor::Green);
    $layer = $grid->save();

    expect($layer->chars)->toHaveCount(0);
});
