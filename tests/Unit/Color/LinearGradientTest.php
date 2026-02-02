<?php

declare(strict_types=1);

use Crumbls\Tui\Color\LinearGradient;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Position\FractionalPosition;

test('linear gradient up', function (): void {
    $color = LinearGradient::from(RgbColor::fromRgb(0, 0, 0));
    $color = $color->addStop(0.5, RgbColor::fromRgb(10, 0, 0));
    expect($color->at(FractionalPosition::at(0.0, 0))->debugName())->toBe('RGB(0, 0, 0)');
    expect($color->at(FractionalPosition::at(0.5, 0))->debugName())->toBe('RGB(10, 0, 0)');
    expect($color->at(FractionalPosition::at(0.25, 0))->debugName())->toBe('RGB(5, 0, 0)');
    expect($color->at(FractionalPosition::at(0.1, 0))->debugName())->toBe('RGB(2, 0, 0)');
    expect($color->at(FractionalPosition::at(0.0, 0))->debugName())->toBe('RGB(0, 0, 0)');
    expect($color->at(FractionalPosition::at(0.75, 0))->debugName())->toBe('RGB(10, 0, 0)');
    expect($color->at(FractionalPosition::at(1, 0))->debugName())->toBe('RGB(10, 0, 0)');
});

test('linear gradient down', function (): void {
    $color = LinearGradient::from(RgbColor::fromRgb(50, 10, 100));
    $color = $color->addStop(0.5, RgbColor::fromRgb(0, 90, 110));
    expect($color->at(FractionalPosition::at(0.25, 0))->debugName())->toBe('RGB(25, 50, 105)');
    expect($color->at(FractionalPosition::at(0.5, 0))->debugName())->toBe('RGB(0, 90, 110)');
});

test('linear gradient three stops', function (): void {
    $color = LinearGradient::from(
        RgbColor::fromRgb(100, 0, 0)
    )->addStop(
        0.5,
        RgbColor::fromRgb(50, 255, 50)
    )->addStop(
        1,
        RgbColor::fromRgb(0, 255, 255)
    );
    expect($color->at(FractionalPosition::at(0.25, 0))->debugName())->toBe('RGB(75, 127, 25)');
    expect($color->at(FractionalPosition::at(0.125, 0))->debugName())->toBe('RGB(87, 63, 12)');
    expect($color->at(FractionalPosition::at(0.75, 0))->debugName())->toBe('RGB(25, 255, 152)');
});

test('rotate zero', function (): void {
    $color = LinearGradient::from(
        RgbColor::fromRgb(0, 0, 0)
    )->addStop(
        1,
        RgbColor::fromRgb(255, 255, 255)
    )->withDegrees(0)->withOrigin(FractionalPosition::at(0.5, 0.5));

    expect($color->at(FractionalPosition::at(0, 0))->debugName())->toBe('RGB(0, 0, 0)');
    expect($color->at(FractionalPosition::at(0.5, 0))->debugName())->toBe('RGB(127, 127, 127)');
    expect($color->at(FractionalPosition::at(1, 0))->debugName())->toBe('RGB(255, 255, 255)');
    expect($color->at(FractionalPosition::at(1, 1))->debugName())->toBe('RGB(255, 255, 255)');
});

test('fraction cannot be less than zero', function (): void {
    $color = LinearGradient::from(
        RgbColor::fromRgb(0, 0, 0)
    )->addStop(
        1,
        RgbColor::fromRgb(255, 127, 0)
    )->withDegrees(300)->withOrigin(FractionalPosition::at(0.5, 0.5));

    expect($color->at(FractionalPosition::at(0, 0))->debugName())->toBe('RGB(0, 0, 0)');
    expect($color->at(FractionalPosition::at(0.5, 0))->debugName())->toBe('RGB(17, 8, 0)');
});

test('bounds', function (): void {
    for ($at = 0; $at <= 1; $at += 0.1) {
        for ($origin = 0; $origin <= 1; $origin += 0.1) {
            for ($d = 0; $d < 360; $d += 45) {
                $color = LinearGradient::from(
                    RgbColor::fromRgb(0, 0, 0)
                )->addStop(
                    1,
                    RgbColor::fromRgb(255, 255, 255)
                )->withDegrees($d)->withOrigin(FractionalPosition::at($origin, $origin));
                $color->at(FractionalPosition::at($at, $at));
            }
        }
    }
    expect(true)->toBeTrue();
});

test('to string', function (): void {
    $color = LinearGradient::from(
        RgbColor::fromRgb(0, 0, 0)
    )->addStop(
        1,
        RgbColor::fromRgb(255, 255, 255)
    )->withDegrees(90)->withOrigin(FractionalPosition::at(0.5, 0.5));

    expect($color->debugName())->toBe('LinearGradient(deg: 90, origin: [0.50, 0.50], stops: [RGB(0, 0, 0)@0.00, RGB(255, 255, 255)@1.00])');
});

test('add stop above 1', function (): void {
    expect(fn () => LinearGradient::from(RgbColor::fromRgb(0, 0, 0))->addStop(2, RgbColor::fromRgb(10, 0, 0)))
        ->toThrow(RuntimeException::class, 'Stop must be a float between 0 and 1, got 2.000000');
});

test('add stop less than 0', function (): void {
    expect(fn () => LinearGradient::from(RgbColor::fromRgb(0, 0, 0))->addStop(-1, RgbColor::fromRgb(10, 0, 0)))
        ->toThrow(RuntimeException::class, 'Stop must be a float between 0 and 1, got -1.000000');
});
