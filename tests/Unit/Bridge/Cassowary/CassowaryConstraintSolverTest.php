<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Widget\Direction;

test('percentage', function (): void {
    $splits = Layout::default()
        ->direction(Direction::Horizontal)
        ->constraints([
            Constraint::percentage(50),
            Constraint::percentage(50),
        ])
        ->split(Area::fromDimensions(100, 100));

    expect($splits->get(0)->toArray())->toBe([0, 0, 50, 100]);
    expect($splits->get(1)->toArray())->toBe([50, 0, 50, 100]);
});

test('multiple percentages', function (): void {
    $splits = Layout::default()
        ->direction(Direction::Horizontal)
        ->constraints([
            Constraint::percentage(50),
            Constraint::percentage(25),
            Constraint::percentage(25),
        ])
        ->split(Area::fromDimensions(100, 100));

    expect($splits->get(0)->toArray())->toBe([0, 0, 50, 100]);
    expect($splits->get(1)->toArray())->toBe([50, 0, 25, 100]);
    expect($splits->get(2)->toArray())->toBe([75, 0, 25, 100]);
});
