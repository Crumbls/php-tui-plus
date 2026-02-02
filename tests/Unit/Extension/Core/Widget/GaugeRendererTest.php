<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;
use Crumbls\Tui\Text\Span;

test('gauge render 0', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 1));
    render($buffer, GaugeWidget::default()->ratio(0));

    expect($buffer->toLines())->toEqual([
        '  0.00%   ',
    ]);
});

test('gauge render 50', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, GaugeWidget::default()->ratio(0.5));

    expect($buffer->toLines())->toEqual([
        '█████     ',
        '█████     ',
        '██50.00%  ',
        '█████     ',
    ]);
});

test('gauge render fi', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 1));
    render($buffer, GaugeWidget::default()->ratio(0.98));

    expect($buffer->toLines())->toEqual([
        '██98.00%█▊',
    ]);
});

test('gauge render 75', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, GaugeWidget::default()->ratio(0.75));

    expect($buffer->toLines())->toEqual([
        '███████▌  ',
        '███████▌  ',
        '██75.00%  ',
        '███████▌  ',
    ]);
});

test('gauge render custom label', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 3));
    render($buffer, GaugeWidget::default()->ratio(1)->label(Span::fromString('Hello')));

    expect($buffer->toLines())->toEqual([
        '██████████',
        '██Hello███',
        '██████████',
    ]);
});
