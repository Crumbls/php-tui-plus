<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Widget\Direction;

test('vertical split by height', function (): void {
    $target = Area::fromScalars(2, 2, 10, 10);
    $chunks = Layout::default()
        ->direction(Direction::Vertical)
        ->constraints([
            Constraint::min(1),
            Constraint::min(1),
            Constraint::max(5),
        ])
        ->split($target);

    expect(array_sum(
        array_map(
            static fn (Area $area): int => $area->height,
            $chunks->toArray()
        )
    ))->toBe($target->height);
});

test('split equally in underspecified case', function (): void {
    $target = Area::fromScalars(100, 100, 10, 10);
    $layout = Layout::default()
        ->direction(Direction::Horizontal)
        ->constraints([
            Constraint::min(2),
            Constraint::min(2),
            Constraint::min(0),
        ])
        ->split($target);

    expect($layout->get(0)->toArray())->toBe([100,100,2,10]);
    expect($layout->get(1)->toArray())->toBe([102,100,2,10]);
    expect($layout->get(2)->toArray())->toBe([104,100,6,10]);
});
