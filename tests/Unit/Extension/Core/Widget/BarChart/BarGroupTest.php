<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\BarChart\Bar;
use Crumbls\Tui\Extension\Core\Widget\BarChart\BarGroup;
use Crumbls\Tui\Text\Line;

test('from', function (): void {
    $group = BarGroup::fromArray(['B0' => 1, 'B1' => 2]);

    expect($group->bars)->toEqual([
        Bar::fromValue(1)->label(Line::fromString('B0')),
        Bar::fromValue(2)->label(Line::fromString('B1')),
    ]);
});
