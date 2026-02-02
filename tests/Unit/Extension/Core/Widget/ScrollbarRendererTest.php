<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarOrientation;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarSymbols;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarWidget;

test('no state', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 7));
    render($buffer, ScrollbarWidget::default());

    expect($buffer->toLines())->toEqual([
        '   ',
        '   ',
        '   ',
        '   ',
        '   ',
        '   ',
        '   ',
    ]);
});

test('vertical left', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 7));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10)));

    expect($buffer->toLines())->toEqual([
        '▲  ',
        '█  ',
        '█  ',
        '║  ',
        '║  ',
        '║  ',
        '▼  ',
    ]);
});

test('vertical right', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 7));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10))->orientation(ScrollbarOrientation::VerticalRight));

    expect($buffer->toLines())->toEqual([
        '  ▲',
        '  █',
        '  █',
        '  ║',
        '  ║',
        '  ║',
        '  ▼',
    ]);
});

test('no beginning symbol', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 7));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10))->beginSymbol(null));

    expect($buffer->toLines())->toEqual([
        '█  ',
        '█  ',
        '█  ',
        '║  ',
        '║  ',
        '║  ',
        '▼  ',
    ]);
});

test('no end symbol', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 7));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10))->endSymbol(null));

    expect($buffer->toLines())->toEqual([
        '▲  ',
        '█  ',
        '█  ',
        '█  ',
        '║  ',
        '║  ',
        '║  ',
    ]);
});

test('double horizontal top', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(7, 3));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10))->orientation(ScrollbarOrientation::HorizontalTop));

    expect($buffer->toLines())->toEqual([
        '◄██═══►',
        '       ',
        '       ',
    ]);
});

test('double horizontal bottom', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(7, 3));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(10))->orientation(ScrollbarOrientation::HorizontalBottom));

    expect($buffer->toLines())->toEqual([
        '       ',
        '       ',
        '◄██═══►',
    ]);
});

test('double horizontal bottom mid', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(7, 3));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(20, 10, 5))->orientation(ScrollbarOrientation::HorizontalBottom));

    expect($buffer->toLines())->toEqual([
        '       ',
        '       ',
        '◄══█══►',
    ]);
});

test('symbol vertical', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(7, 3));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(20, 10, 5))->symbols(ScrollbarSymbols::vertical()));

    expect($buffer->toLines())->toEqual([
        '↑      ',
        '█      ',
        '↓      ',
    ]);
});

test('symbol horizontal', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(7, 3));
    render($buffer, ScrollbarWidget::default()->state(new ScrollbarState(20, 10, 5))->orientation(ScrollbarOrientation::HorizontalTop)->symbols(ScrollbarSymbols::horizontal()));

    expect($buffer->toLines())->toEqual([
        '←──█──→',
        '       ',
        '       ',
    ]);
});
