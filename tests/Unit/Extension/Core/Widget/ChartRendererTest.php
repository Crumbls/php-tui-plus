<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Extension\Core\Widget\Chart\Axis;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Extension\Core\Widget\Chart\DataSet;
use Crumbls\Tui\Extension\Core\Widget\ChartWidget;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Span;

function series(int ...$points): array
{
    return array_map(static function (int $x, int $y): array {
        return [$x, $y];
    }, range(0, count($points) - 1), $points);
}

test('render', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(series(0, 1, 2, 1, 0, -1, -2, -1))
    )
        ->xAxis(Axis::default()->bounds(AxisBounds::new(0, 7)))
        ->yAxis(
            Axis::default()->bounds(AxisBounds::new(-2, 2))
        );

    expect(renderToLines($chart))->toEqual([
        '  •     ',
        ' • •    ',
        '•   •   ',
        '     • •',
        '      • ',
    ]);
});

test('render calculate bounds', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(series(0, 1, 2, 1, 0, -1, -2, -1))
    );

    expect(renderToLines($chart))->toEqual([
        '  •     ',
        ' • •    ',
        '•   •   ',
        '     • •',
        '      • ',
    ]);
});

test('render axis lines', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(series(0, 1, 2, 1, 0, -1, -2, -1))
    )->xAxis(
        Axis::default()->bounds(AxisBounds::new(0, 7))->labels()
    )->yAxis(
        Axis::default()->bounds(AxisBounds::new(-2, 2))->labels()
    );

    expect(renderToLines($chart, 8, 6))->toEqual([
        '│ •     ',
        '│• •    ',
        '│•  •   ',
        '│    • •',
        '│     • ',
        '└───────',
    ]);
});

test('render x axis labels', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(series(0, 1, 2, 1, 0, -1, -2, -1))
    )->xAxis(
        Axis::default()->bounds(AxisBounds::new(0, 7))->labels(Span::fromString('1'), Span::fromString('2'))
    )->yAxis(
        Axis::default()->bounds(AxisBounds::new(-2, 2))
    );

    expect(renderToLines($chart, 8, 6))->toEqual([
        ' •••    ',
        '•   •   ',
        '     • •',
        '      • ',
        '────────',
        '1    2  ',
    ]);
});

test('render x axis one label', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(series(0, 1, 2, 1, 0, -1, -2, -1))
    )->xAxis(
        Axis::default()->bounds(AxisBounds::new(0, 7))->labels(Span::fromString('1'))
    )->yAxis(
        Axis::default()->bounds(AxisBounds::new(-2, 2))
    );

    expect(renderToLines($chart, 8, 6))->toEqual([
        ' •••    ',
        '•   •   ',
        '     • •',
        '      • ',
        '────────',
        '1       ',
    ]);
});

test('render many x labels', function (): void {
    $chart = ChartWidget::new()
        ->datasets(
            DataSet::new('data1')
                ->marker(Marker::Dot)
                ->style(Style::default()->fg(AnsiColor::Green))
                ->data(series(0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1))
        )
        ->xAxis(
            Axis::default()->bounds(AxisBounds::new(0, 11))->labels(
                Span::fromString('1'),
                Span::fromString('2'),
                Span::fromString('3'),
                Span::fromString('4'),
            )
        )
        ->yAxis(
            Axis::default()->bounds(AxisBounds::new(0, 1))
        );

    expect(renderToLines($chart, 12, 4))->toEqual([
        ' • • • • • •',
        '• • • • • • ',
        '────────────',
        '1   2  3  4 ',
    ]);
});

test('render y axis labels', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(
                array_map(static function (int $x, int $y): array {
                    return [$x, $y];
                }, range(0, 7), [0, 1, 2, 1, 0, -1, -2, -1])
            )
    )->xAxis(
        Axis::default()->bounds(AxisBounds::new(0, 7))
    )->yAxis(
        Axis::default()->bounds(AxisBounds::new(-2, 2))->labels(Span::fromString('1'), Span::fromString('2'))
    );

    expect(renderToLines($chart, 8, 6))->toEqual([
        '2│ •    ',
        ' │• •   ',
        ' │• •   ',
        ' │   • •',
        ' │      ',
        '1│    • ',
    ]);
});

test('render many x and y labels', function (): void {
    $chart = ChartWidget::new()
        ->datasets(
            DataSet::new('data1')
                ->marker(Marker::Dot)
                ->style(Style::default()->fg(AnsiColor::Green))
                ->data(series(0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1))
        )
        ->xAxis(
            Axis::default()->bounds(AxisBounds::new(0, 11))->labels(
                Span::fromString('1'),
                Span::fromString('2'),
                Span::fromString('3'),
                Span::fromString('4'),
            )
        )
        ->yAxis(
            Axis::default()->bounds(AxisBounds::new(0, 1))->labels(
                Span::fromString('one'),
                Span::fromString('two'),
                Span::fromString('three'),
                Span::fromString('four'),
            )
        );

    expect(renderToLines($chart, 24, 8))->toEqual([
        ' four│ •  •  •  •  •   •',
        '     │                  ',
        'three│                  ',
        '     │                  ',
        '  two│                  ',
        '  one│•  •  •  •  •  •  ',
        '     └──────────────────',
        '     1      2   3    4  ',
    ]);
});

test('render y axis one label', function (): void {
    $chart = ChartWidget::new(
        DataSet::new('data1')
            ->marker(Marker::Dot)
            ->style(Style::default()->fg(AnsiColor::Green))
            ->data(
                array_map(static function (int $x, int $y): array {
                    return [$x, $y];
                }, range(0, 7), [0, 1, 2, 1, 0, -1, -2, -1])
            )
    )->xAxis(
        Axis::default()->bounds(AxisBounds::new(0, 7))
    )->yAxis(
        Axis::default()->bounds(AxisBounds::new(-2, 2))->labels(Span::fromString('1'))
    );

    expect(renderToLines($chart, 8, 6))->toEqual([
        ' │ •    ',
        ' │• •   ',
        ' │• •   ',
        ' │   • •',
        ' │      ',
        '1│    • ',
    ]);
});
