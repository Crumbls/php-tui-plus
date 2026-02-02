<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Widget\Margin;

test('inner empty', function (): void {
    $a = Area::empty();

    expect($a->inner(Margin::fromScalars(10, 10)))->toEqual(Area::empty());
});

test('inner', function (): void {
    $a = Area::fromDimensions(10, 10);

    expect($a->inner(Margin::all(2)))->toEqual(Area::fromScalars(2, 2, 6, 6));
});

test('with vertical margin', function (): void {
    $a = Area::fromDimensions(10, 10);

    expect($a->inner(Margin::vertical(2)))->toEqual(Area::fromScalars(0, 2, 10, 6));
});

test('with horizontal margin', function (): void {
    $a = Area::fromDimensions(10, 10);

    expect($a->inner(Margin::horizontal(2)))->toEqual(Area::fromScalars(2, 0, 6, 10));
});
