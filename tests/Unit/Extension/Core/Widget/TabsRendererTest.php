<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\TabsWidget;
use Crumbls\Tui\Text\Line;

test('zero tabs', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, TabsWidget::default());

    expect($buffer->toLines())->toEqual([
        '                    ',
        '                    ',
    ]);
});

test('one tab', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, TabsWidget::default()
        ->titles(
            Line::fromString('Tab 1'),
        ));

    expect($buffer->toLines())->toEqual([
        ' Tab 1              ',
        '                    ',
    ]);
});

test('two tabs', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, TabsWidget::default()
        ->titles(
            Line::fromString('Tab 1'),
            Line::fromString('Tab 2'),
        ));

    expect($buffer->toLines())->toEqual([
        ' Tab 1 │ Tab 2      ',
        '                    ',
    ]);
});

test('select tabs', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, TabsWidget::default()
        ->select(1)
        ->titles(
            Line::fromString('Tab 1'),
            Line::fromString('Tab 2'),
        ));

    expect($buffer->toLines())->toEqual([
        ' Tab 1 │ Tab 2      ',
        '                    ',
    ]);
});

test('select out of range', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, TabsWidget::default()
        ->select(100)
        ->titles(
            Line::fromString('Tab 1'),
            Line::fromString('Tab 2'),
        ));

    expect($buffer->toLines())->toEqual([
        ' Tab 1 │ Tab 2      ',
        '                    ',
    ]);
});
