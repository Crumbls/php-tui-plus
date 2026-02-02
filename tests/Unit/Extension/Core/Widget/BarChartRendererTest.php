<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\BarChart\Bar;
use Crumbls\Tui\Extension\Core\Widget\BarChart\BarGroup;
use Crumbls\Tui\Extension\Core\Widget\BarChartWidget;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Widget\Direction;

test('zero dimension', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(0, 0));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromArray(['B0' => 1, 'B1' => 2])
    ));

    expect($buffer->toLines())->toEqual(['']);
});

test('zero values', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromArray(['B0' => 0, 'B1' => 0])
    ));

    expect($buffer->toLines())->toEqual([
        '          ',
        '          ',
        '          ',
        '0 0       ',
        'B B       ',
    ]);
});

test('negative values', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        /** @phpstan-ignore-next-line */
        BarGroup::fromArray(['B0' => -1, 'B1' => 2])
    ));

    expect($buffer->toLines())->toEqual([
        '  █       ',
        '  █       ',
        '  █       ',
        '  2       ',
        'B B       ',
    ]);
});

test('vertical barchart', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromArray(['B0' => 1, 'B1' => 2])
    ));

    expect($buffer->toLines())->toEqual([
        '  █       ',
        '▄ █       ',
        '█ █       ',
        '1 2       ',
        'B B       ',
    ]);
});

test('horizontal barchart', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromArray(['B0' => 1, 'B1' => 2])
    )->direction(Direction::Horizontal));

    expect($buffer->toLines())->toEqual([
        'B0 1██    ',
        '          ',
        'B1 2██████',
        '          ',
        '          ',
    ]);
});

test('text labels and custom values', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromBars(
            Bar::fromValue(1)->textValue('A')->label(Line::fromString('X0')),
            Bar::fromValue(2)->textValue('B')->label(Line::fromString('X1')),
        )
    )->direction(Direction::Horizontal));

    expect($buffer->toLines())->toEqual([
        'X0 A██    ',
        '          ',
        'X1 B██████',
        '          ',
        '          ',
    ]);
});

test('with style', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromBars(
            Bar::fromValue(1)->textValue('A')->label(Line::fromString('X0'))->style(Style::default()),
            Bar::fromValue(2)->textValue('B')->label(Line::fromString('X1')),
        )
    )->direction(Direction::Horizontal));

    expect($buffer->toLines())->toEqual([
        'X0 A██    ',
        '          ',
        'X1 B██████',
        '          ',
        '          ',
    ]);
});

test('horizontal chart with width > 1', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    render($buffer, BarChartWidget::default()->barWidth(4)->data(
        BarGroup::fromBars(
            Bar::fromValue(1)->textValue('A')->label(Line::fromString('X0'))->style(Style::default()),
            Bar::fromValue(2)->textValue('B')->label(Line::fromString('X1')),
        ),
    )->direction(Direction::Horizontal));

    expect($buffer->toLines())->toEqual([
        '   ███    ',
        '   ███    ',
        'X0 A██    ',
        '   ███    ',
        '          ',
        '   ███████',
        '   ███████',
        'X1 B██████',
        '   ███████',
        '          ',
    ]);
});

test('wider than dimensions', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromBars(
            Bar::fromValue(1)->textValue('A')->label(Line::fromString('X0'))->style(Style::default()),
            Bar::fromValue(2)->textValue('B')->label(Line::fromString('X1')),
        )
    )->direction(Direction::Horizontal)->barGap(10));

    expect($buffer->toLines())->toEqual([
        'X0 A██    ',
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});

test('taller than dimensions', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 5));
    render($buffer, BarChartWidget::default()->data(
        BarGroup::fromBars(
            Bar::fromValue(1)->textValue('A')->label(Line::fromString('X0'))->style(Style::default()),
            Bar::fromValue(2)->textValue('B')->label(Line::fromString('X1')),
        )
    )->direction(Direction::Vertical)->barGap(10));

    expect($buffer->toLines())->toEqual([
        '          ',
        '▄         ',
        '█         ',
        'A         ',
        'X         ',
    ]);
});
