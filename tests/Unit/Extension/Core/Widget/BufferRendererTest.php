<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\Buffer\BufferContext;
use Crumbls\Tui\Extension\Core\Widget\BufferWidget;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Span;

test('write to buffer', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    render($buffer, BufferWidget::new(static function (BufferContext $context): void {
        $context->buffer->putLine(Position::at(0, 0), Line::fromString('Hello'), 5);
    }));

    expect($buffer->toLines())->toEqual([
        'Hello     ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});

test('write to buffer in block', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    render($buffer, BlockWidget::default()->widget(
        BufferWidget::new(static function (BufferContext $context): void {
            $context->buffer->putLine(Position::at($context->area->left(), $context->area->top()), Line::fromString('Hello'), 5);
        })
    )->padding(Padding::fromScalars(1, 1, 1, 1)));

    expect($buffer->toLines())->toEqual([
        '          ',
        ' Hello    ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});

test('overflow', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 10));
    render($buffer, BlockWidget::default()->widget(
        BufferWidget::new(static function (BufferContext $context): void {
            $context->buffer->putSpan(
                Position::at($context->area->left(), $context->area->top()),
                Span::fromString(str_repeat('Hello', 10)),
                10
            );
        })
    )->padding(Padding::fromScalars(1, 1, 1, 1)));

    expect($buffer->toLines())->toEqual([
        '          ',
        ' HelloHel ',
        ' lo       ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});
