<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Text\SpanParser;

test('parse one tag', function (): void {
    $spans = SpanParser::new()->parse('<fg=green;bg=blue;options=bold,italic>Hello</> World');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
    expect($firstSpan->style->bg)->toBe(AnsiColor::Blue);
    expect(($firstSpan->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($firstSpan->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe(' World');
    expect($secondSpan->style->fg)->toBeNull();
    expect($secondSpan->style->bg)->toBeNull();
    expect($secondSpan->style->addModifiers)->toBe(Modifier::NONE);
    expect($secondSpan->style->subModifiers)->toBe(Modifier::NONE);
});

test('parse nested tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green;bg=blue;options=bold,italic>Hello <fg=white;bg=red>Wor</>ld</> PHP');

    expect($spans)->toHaveCount(4);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello ');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
    expect($firstSpan->style->bg)->toBe(AnsiColor::Blue);
    expect(($firstSpan->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($firstSpan->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('Wor');
    expect($secondSpan->style->bg)->toBe(AnsiColor::Red);
    expect($secondSpan->style->fg)->toBe(AnsiColor::White);
    expect(($secondSpan->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($secondSpan->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $thirdSpan = $spans[2];
    expect($thirdSpan->content)->toBe('ld');
    expect($thirdSpan->style->bg)->toBe(AnsiColor::Blue);
    expect($thirdSpan->style->fg)->toBe(AnsiColor::Green);
    expect(($thirdSpan->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();
    expect(($thirdSpan->style->addModifiers & Modifier::ITALIC) === Modifier::ITALIC)->toBeTrue();

    $fourthSpan = $spans[3];
    expect($fourthSpan->content)->toBe(' PHP');
    expect($fourthSpan->style->fg)->toBeNull();
    expect($fourthSpan->style->bg)->toBeNull();
    expect($fourthSpan->style->addModifiers)->toBe(Modifier::NONE);
    expect($fourthSpan->style->subModifiers)->toBe(Modifier::NONE);
});

test('parse angle brackets', function (): void {
    $spans = SpanParser::new()->parse('<bg=white><fg=blue>PHP</> <fg=red><></> <fg=yellow>Rust</></> 1 > 2 && 2 < 3');
    expect($spans)->toHaveCount(6);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('PHP');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Blue);
    expect($firstSpan->style->bg)->toBe(AnsiColor::White);

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe(' ');
    expect($secondSpan->style->fg)->toBeNull();
    expect($secondSpan->style->bg)->toBe(AnsiColor::White);

    $thirdSpan = $spans[2];
    expect($thirdSpan->content)->toBe('<>');
    expect($thirdSpan->style->fg)->toBe(AnsiColor::Red);
    expect($thirdSpan->style->bg)->toBe(AnsiColor::White);

    $fourthSpan = $spans[3];
    expect($fourthSpan->content)->toBe(' ');
    expect($fourthSpan->style->fg)->toBeNull();
    expect($fourthSpan->style->bg)->toBe(AnsiColor::White);

    $fifthSpan = $spans[4];
    expect($fifthSpan->content)->toBe('Rust');
    expect($fifthSpan->style->fg)->toBe(AnsiColor::Yellow);
    expect($fifthSpan->style->bg)->toBe(AnsiColor::White);

    $sixthSpan = $spans[5];
    expect($sixthSpan->content)->toBe(' 1 > 2 && 2 < 3');
    expect($sixthSpan->style->fg)->toBeNull();
    expect($sixthSpan->style->bg)->toBeNull();
});

test('parse with break line', function (): void {
    $spans = SpanParser::new()->parse("<fg=blue>PHP</>\n<fg=yellow>Rust</>");
    expect($spans)->toHaveCount(3);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('PHP');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Blue);
    expect($firstSpan->style->bg)->toBeNull();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe("\n");
    expect($secondSpan->style->fg)->toBeNull();
    expect($secondSpan->style->bg)->toBeNull();

    $thirdSpan = $spans[2];
    expect($thirdSpan->content)->toBe('Rust');
    expect($thirdSpan->style->fg)->toBe(AnsiColor::Yellow);
    expect($thirdSpan->style->bg)->toBeNull();
});

test('parse with duplicate closing tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green>Hello</>World</></>');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('World');
    expect($secondSpan->style->fg)->toBeNull();
});

test('parse handling of escaped tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green>Hello \<strong class="foo">some info\</strong> World</>');
    expect($spans)->toHaveCount(1);
    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello <strong class="foo">some info</strong> World');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);

    $spans = SpanParser::new()->parse('<fg=green>Hello \<strong class="foo"\>some info\</strong\> World</>');
    expect($spans)->toHaveCount(1);
    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello <strong class="foo">some info</strong> World');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);

    $spans = SpanParser::new()->parse('Hello \<fg=blue\>World\</fg\>');
    expect($spans)->toHaveCount(1);
    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello <fg=blue>World</fg>');
});

test('parse with empty parameters', function (): void {
    $spans = SpanParser::new()->parse('<fg = >Hello <options>World</></>');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello ');
    expect($firstSpan->style->fg)->toBeNull();
    expect($firstSpan->style->bg)->toBeNull();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('World');
    expect($secondSpan->style->fg)->toBeNull();
    expect($secondSpan->style->bg)->toBeNull();
});

test('parse empty string', function (): void {
    $spans = SpanParser::new()->parse('');
    expect($spans)->toHaveCount(0);
});

test('parse with invalid color name', function (): void {
    expect(fn () => SpanParser::new()->parse('<fg=foo>Hello</>'))
        ->toThrow(InvalidArgumentException::class, 'Unknown color name "foo"');
});

test('malformed tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green>Hello <bg=blue World</fg=green>');
    expect($spans)->toHaveCount(1);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello <bg=blue World');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
    expect($firstSpan->style->bg)->toBeNull();
});

test('overlapping tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green><bg=blue>Hello</fg=green> World</bg=blue>');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
    expect($firstSpan->style->bg)->toBe(AnsiColor::Blue);

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe(' World');
    expect($secondSpan->style->fg)->toBe(AnsiColor::Green);
    expect($secondSpan->style->bg)->toBeNull();
});

test('nested same style tags', function (): void {
    $spans = SpanParser::new()->parse('<fg=green>Hello <fg=green>World</fg=green> Again</fg=green>');
    expect($spans)->toHaveCount(3);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello ');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
    expect($firstSpan->style->bg)->toBeNull();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('World');
    expect($secondSpan->style->fg)->toBe(AnsiColor::Green);
    expect($secondSpan->style->bg)->toBeNull();

    $thirdSpan = $spans[2];
    expect($thirdSpan->content)->toBe(' Again');
    expect($thirdSpan->style->fg)->toBe(AnsiColor::Green);
    expect($thirdSpan->style->bg)->toBeNull();
});

test('empty tags', function (): void {
    $spans = SpanParser::new()->parse('Hello <fg=green></fg=green> World');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello ');
    expect($firstSpan->style->fg)->toBeNull();
    expect($firstSpan->style->bg)->toBeNull();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe(' World');
    expect($secondSpan->style->fg)->toBeNull();
    expect($secondSpan->style->bg)->toBeNull();
});

test('parse hex colors', function (): void {
    $spans = SpanParser::new()->parse('<fg=#ff0000;bg=#ccc>Hello</>');
    expect($spans)->toHaveCount(1);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello');
    expect($firstSpan->style->fg?->debugName())->toBe(RgbColor::fromHex('#ff0000')->debugName());
    expect($firstSpan->style->bg?->debugName())->toBe(RgbColor::fromHex('#ccc')->debugName());
});

test('parse break lines', function (): void {
    $text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

    $spans = SpanParser::new()->parse(sprintf('<fg=green>%s</>', $text));
    expect($spans)->toHaveCount(1);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe($text);
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);
});

test('using wrong separator', function (): void {
    expect(fn () => SpanParser::new()->parse('<fg=white bg=red>Hello</> World'))
        ->toThrow(InvalidArgumentException::class);
});

test('multi width characters', function (): void {
    $spans = SpanParser::new()->parse('<fg=green>Hello 你好 PHP 🐘<fg=white>PHP 🐘 Hello 你好</></>');
    expect($spans)->toHaveCount(2);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Hello 你好 PHP 🐘');
    expect($firstSpan->style->fg)->toBe(AnsiColor::Green);

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('PHP 🐘 Hello 你好');
    expect($secondSpan->style->fg)->toBe(AnsiColor::White);

    $spans = SpanParser::new()->parse('Welcome to the <fg=white;options=bold>PHP-TUI 🐘</> demo application.');
    expect($spans)->toHaveCount(3);

    $firstSpan = $spans[0];
    expect($firstSpan->content)->toBe('Welcome to the ');
    expect($firstSpan->style->fg)->toBeNull();

    $secondSpan = $spans[1];
    expect($secondSpan->content)->toBe('PHP-TUI 🐘');
    expect($secondSpan->style->fg)->toBe(AnsiColor::White);
    expect(($secondSpan->style->addModifiers & Modifier::BOLD) === Modifier::BOLD)->toBeTrue();

    $thirdSpan = $spans[2];
    expect($thirdSpan->content)->toBe(' demo application.');
    expect($thirdSpan->style->fg)->toBeNull();
});
