<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Text;

test('raw', function (): void {
    $text = Text::fromString("The first line\nThe second line");
    expect($text->lines)->toHaveCount(2);
});

test('styled', function (): void {
    $style = Style::default();
    $style->fg = AnsiColor::Red;
    $text = Text::styled("The first line\nThe second line", $style);
    expect($text->lines)->toHaveCount(2);
    expect($text->lines[0]->spans)->toHaveCount(1);
    expect($text->lines[0]->spans[0]->style->fg)->toBe(AnsiColor::Red);
});

test('parse', function (): void {
    $text = Text::parse("<fg=blue;bg=white;options=bold,italic>Hello</>\n<fg=white;bg=green;options=italic>World</>");
    expect($text->lines)->toHaveCount(2);

    $firstLine = $text->lines[0];
    expect($firstLine->spans)->toHaveCount(1);
    expect($firstLine->spans[0]->style->fg)->toBe(AnsiColor::Blue);
    expect($firstLine->spans[0]->style->bg)->toBe(AnsiColor::White);
    expect(($firstLine->spans[0]->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($firstLine->spans[0]->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $secondLine = $text->lines[1];
    expect($secondLine->spans)->toHaveCount(1);
    expect($secondLine->spans[0]->style->fg)->toBe(AnsiColor::White);
    expect($secondLine->spans[0]->style->bg)->toBe(AnsiColor::Green);
    expect(($secondLine->spans[0]->style->addModifiers & Modifier::BOLD) === Modifier::NONE)->toBeTrue();
    expect(($secondLine->spans[0]->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();
});

test('parse text with break lines', function (): void {
    $text = Text::parse("<fg=blue;bg=white;options=bold,italic>Hel\nlo</>\n<fg=white;bg=green;options=italic>Wor\nld</>");
    expect($text->lines)->toHaveCount(2);

    $firstLine = $text->lines[0];
    expect($firstLine->spans[0]->content)->toBe("Hel\nlo");
    expect($firstLine->spans)->toHaveCount(1);
    expect($firstLine->spans[0]->style->fg)->toBe(AnsiColor::Blue);
    expect($firstLine->spans[0]->style->bg)->toBe(AnsiColor::White);
    expect(($firstLine->spans[0]->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($firstLine->spans[0]->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $secondLine = $text->lines[1];
    expect($secondLine->spans[0]->content)->toBe("Wor\nld");
    expect($secondLine->spans)->toHaveCount(1);
    expect($secondLine->spans[0]->style->fg)->toBe(AnsiColor::White);
    expect($secondLine->spans[0]->style->bg)->toBe(AnsiColor::Green);
    expect(($secondLine->spans[0]->style->addModifiers & Modifier::BOLD) === Modifier::NONE)->toBeTrue();
    expect(($secondLine->spans[0]->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();
});
