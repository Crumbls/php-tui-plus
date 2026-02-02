<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Grid\HalfBlockGrid;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Position\Position;

test('zero size', function (): void {
    $grid = HalfBlockGrid::new(0, 0);
    $grid->paint(Position::at(1, 1), AnsiColor::Green);
    $layer = $grid->save();

    expect($layer->chars)->toHaveCount(0);
});
