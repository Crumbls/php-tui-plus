<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Position\Position;

test('returns index for position', function (): void {
    expect((new Position(5, 5))->toIndex(Area::fromScalars(0, 0, 10, 10)))->toBe(55);
});

test('throws exception if out of range', function (): void {
    expect(fn () => (new Position(15, 5))->toIndex(Area::fromScalars(0, 0, 10, 10)))
        ->toThrow(OutOfBoundsException::class, 'Position (15,5) outside of area @(0,0) of 10x10 when trying to get index');
});

test('creates position from index', function (): void {
    $position = Position::fromIndex(55, Area::fromScalars(0, 0, 10, 10));
    expect($position->x)->toBe(5);
    expect($position->y)->toBe(5);
});

test('throws exception if index out of range', function (): void {
    expect(fn () => Position::fromIndex(100, Area::fromScalars(0, 0, 10, 10)))
        ->toThrow(OutOfBoundsException::class, 'outside of area');
});

test('throws exception if negative x', function (): void {
    expect(fn () => Position::at(-1, 2))
        ->toThrow(RuntimeException::class, 'Neither X nor Y values can be less than zero, got [-1, 2]');
});

test('throws exception if negative y', function (): void {
    expect(fn () => Position::at(1, -2))
        ->toThrow(RuntimeException::class, 'Neither X nor Y values can be less than zero, got [1, -2]');
});
