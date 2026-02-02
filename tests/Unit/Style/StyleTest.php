<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;

test('default', function (): void {
    $style = Style::default();

    expect($style->fg)->toBeNull();
    expect($style->bg)->toBeNull();
    expect($style->underline)->toBeNull();
    expect($style->addModifiers)->toBe(Modifier::NONE);
    expect($style->subModifiers)->toBe(Modifier::NONE);
});

test('fg', function (): void {
    $style = Style::default()->fg(AnsiColor::Red);

    expect($style->fg)->toBe(AnsiColor::Red);
});

test('bg', function (): void {
    $style = Style::default()->bg(AnsiColor::Blue);

    expect($style->bg)->toBe(AnsiColor::Blue);
});

test('add modifier', function (): void {
    $style = Style::default()->addModifier(Modifier::BOLD);

    expect(($style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
});

test('sub modifier', function (): void {
    $style = Style::default()->removeModifier(Modifier::ITALIC);

    expect(($style->subModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();
});

test('patch', function (): void {
    $style1 = Style::default()->bg(AnsiColor::Red);
    $style2 = Style::default()
                ->fg(AnsiColor::Blue)
                ->addModifier(Modifier::BOLD)
                ->addModifier(Modifier::UNDERLINED);

    $combined = $style1->patchStyle($style2);

    expect($combined->subModifiers)->toBe(Modifier::NONE);

    expect($combined->addModifiers)->toBe(Modifier::BOLD | Modifier::UNDERLINED);

    expect((string) $combined)->toBe((string) Style::default()->patchStyle($style1)->patchStyle($style2));

    expect($combined->fg)->toBe(AnsiColor::Blue);
    expect($combined->bg)->toBe(AnsiColor::Red);

    $combined2 = Style::default()->patchStyle($combined)->patchStyle(
        Style::default()
            ->removeModifier(Modifier::BOLD)
            ->addModifier(Modifier::ITALIC),
    );

    expect($combined2->subModifiers)->toBe(Modifier::BOLD);

    expect($combined2->addModifiers)->toBe(Modifier::ITALIC | Modifier::UNDERLINED);

    expect($combined->fg)->toBe(AnsiColor::Blue);
    expect($combined->bg)->toBe(AnsiColor::Red);
});

test('to string', function (): void {
    $style = Style::default()
                ->bg(AnsiColor::Red)
                ->underline(AnsiColor::Blue)
                ->addModifier(Modifier::BOLD)
                ->removeModifier(Modifier::ITALIC)
                ->removeModifier(Modifier::UNDERLINED);

    $expectedString = sprintf(
        'Style(fg:%s,bg: %s,u:%s,+mod:%d,-mod:%d)',
        '-',
        AnsiColor::Red->debugName(),
        AnsiColor::Blue->debugName(),
        Modifier::BOLD,
        Modifier::ITALIC | Modifier::UNDERLINED,
    );

    expect((string) $style)->toBe($expectedString);
});
