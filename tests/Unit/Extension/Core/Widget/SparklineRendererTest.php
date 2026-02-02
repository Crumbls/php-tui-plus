<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Sparkline\RenderDirection;
use Crumbls\Tui\Extension\Core\Widget\SparklineWidget;

test('default', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, SparklineWidget::default());

    expect($buffer->toLines())->toEqual([
        '                    ',
        '                    ',
    ]);
});

test('sparkline', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(12, 1));
    render($buffer, SparklineWidget::fromData(...range(0, 8)));

    expect($buffer->toLines())->toEqual([
        ' ▁▂▃▄▅▆▇█   ',
    ]);
});

test('right to left', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(12, 1));
    render($buffer, SparklineWidget::fromData(...range(0, 8))->direction(RenderDirection::RightToLeft));

    expect($buffer->toLines())->toEqual([
        '   █▇▆▅▄▃▂▁ ',
    ]);
});

test('taller', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(12, 2));
    render($buffer, SparklineWidget::fromData(...range(0, 8))->direction(RenderDirection::RightToLeft));

    expect($buffer->toLines())->toEqual([
        '   █▆▄▂     ',
        '   █████▆▄▂ ',
    ]);
});

test('with max', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(12, 1));
    render($buffer, SparklineWidget::fromData(...range(0, 8))->direction(RenderDirection::RightToLeft)->max(20));

    expect($buffer->toLines())->toEqual([
        '   ▃▃▂▂▂▁▁  ',
    ]);
});
