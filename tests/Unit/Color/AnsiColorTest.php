<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;

test('from index', function (): void {
    expect(AnsiColor::from(0))->toBe(AnsiColor::Black);
    expect(AnsiColor::from(15))->toBe(AnsiColor::White);
});

test('from index out of bounds', function (): void {
    expect(fn () => AnsiColor::from(16))->toThrow(ValueError::class);
});
