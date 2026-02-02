<?php

declare(strict_types=1);

use Crumbls\Tui\Color\RgbColor;

test('from rgb out of range', function (): void {
    expect(fn () => RgbColor::fromRgb(-1, 0, 0))
        ->toThrow(OutOfBoundsException::class, 'red must be in range 0-255 got -1');
});

test('from hsv', function (): void {
    expect(RgbColor::fromHsv(72, 83, 60))->toEqual(RgbColor::fromRgb(128, 153, 26));
    expect(RgbColor::fromHsv(360, 100, 100))->toEqual(RgbColor::fromRgb(0, 0, 0));
    expect(RgbColor::fromHsv(0, 0, 0))->toEqual(RgbColor::fromRgb(0, 0, 0));
});

test('from out of range', function (): void {
    expect(fn () => RgbColor::fromHsv(1000, 0, 0))
        ->toThrow(OutOfBoundsException::class, 'hue must be in range 0-360 got 1000');
});

test('saturation out of range', function (): void {
    expect(fn () => RgbColor::fromHsv(0, -2, 0))
        ->toThrow(OutOfBoundsException::class, 'saturation must be in range 0-100 got -2');
});

test('from hex with valid color', function (): void {
    $color = RgbColor::fromHex('#1a2b3c');
    expect($color->r)->toBe(26);
    expect($color->g)->toBe(43);
    expect($color->b)->toBe(60);

    $color = RgbColor::fromHex('#d7890b');
    expect($color->r)->toBe(215);
    expect($color->g)->toBe(137);
    expect($color->b)->toBe(11);
});

test('from hex with valid short color', function (): void {
    $color = RgbColor::fromHex('#cec');
    expect($color->r)->toBe(204);
    expect($color->g)->toBe(238);
    expect($color->b)->toBe(204);
});

test('from hex with invalid color', function (): void {
    expect(fn () => RgbColor::fromHex('#foo'))
        ->toThrow(InvalidArgumentException::class);
});

test('from hex without hash', function (): void {
    $color = RgbColor::fromHex('123456');
    expect($color->r)->toBe(18);
    expect($color->g)->toBe(52);
    expect($color->b)->toBe(86);
});

test('from hex with invalid length', function (): void {
    expect(fn () => RgbColor::fromHex('#12'))
        ->toThrow(InvalidArgumentException::class);
});
