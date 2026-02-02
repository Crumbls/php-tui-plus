<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Position\FloatPosition;

test('out of bounds', function (): void {
    expect(FloatPosition::at(0, 0)->outOfBounds(new AxisBounds(0, 10), new AxisBounds(0, 10)))->toBeFalse();
    expect(FloatPosition::at(-1, 0)->outOfBounds(new AxisBounds(0, 10), new AxisBounds(0, 10)))->toBeTrue();
});
