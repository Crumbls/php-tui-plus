<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Text\Span;

test('fg bg', function (): void {
    $span = Span::fromString('Hello')->fg(AnsiColor::Blue)->bg(AnsiColor::Red);

    expect($span->style->fg)->toBe(AnsiColor::Blue);
    expect($span->style->bg)->toBe(AnsiColor::Red);
});

dataset('modifiers', [
    [Modifier::BOLD, 'bold'],
    [Modifier::DIM, 'dim'],
    [Modifier::ITALIC, 'italic'],
    [Modifier::UNDERLINED, 'underlined'],
    [Modifier::SLOWBLINK, 'slowBlink'],
    [Modifier::RAPIDBLINK, 'rapidBlink'],
    [Modifier::REVERSED, 'reversed'],
    [Modifier::HIDDEN, 'hidden'],
    [Modifier::CROSSEDOUT, 'crossedOut'],
]);

test('modifiers add', function (int $modifier, string $methodName): void {
    $span = Span::fromString('Hello')->$methodName();
    expect(($span->style->addModifiers & $modifier) === $modifier)->toBeTrue();
})->with('modifiers');

test('modifiers remove', function (int $modifier, string $methodName): void {
    $span = Span::fromString('Hello')->$methodName(false);
    expect(($span->style->subModifiers & $modifier) === $modifier)->toBeTrue();
})->with('modifiers');

dataset('colors', array_map(
    static fn (AnsiColor $color): array => [$color, $color->name],
    array_filter(AnsiColor::cases(), static fn (AnsiColor $color): bool => $color !== AnsiColor::Reset)
));

test('fg colors', function (AnsiColor $color, string $colorName): void {
    $methodName = lcfirst($colorName);
    $span = Span::fromString('Hello')->$methodName();
    expect($span->style->fg)->toBe($color);
})->with('colors');

test('bg colors', function (AnsiColor $color, string $colorName): void {
    $methodName = sprintf('on%s', $colorName);
    $span = Span::fromString('Hello')->$methodName();
    expect($span->style->bg)->toBe($color);
})->with('colors');
