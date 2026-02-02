<?php

declare(strict_types=1);

use Crumbls\Tui\Position\FractionalPosition;

test('rotate', function (): void {
    expect(FractionalPosition::at(0.5, 0.5)->rotate(deg2rad(90)))
        ->toEqualWithDelta(FractionalPosition::at(-0.5, 0.5), 0.2);
});
