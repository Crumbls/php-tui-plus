<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Span;

test('to styled graphemes', function (): void {
    $span = Span::fromString('Hello')->blue();

    $baseStyle = Style::default()->fg(AnsiColor::Red);
    $styledGraphemes = $span->toStyledGraphemes($baseStyle);

    expect($styledGraphemes)->toHaveCount(5);

    foreach ($styledGraphemes as $i => $grapheme) {
        expect($grapheme->style->fg)->toBe(AnsiColor::Blue);
        expect($grapheme->symbol)->toBe($span->content[$i]);
    }

    expect($baseStyle->fg)->toBe(AnsiColor::Red);
});
