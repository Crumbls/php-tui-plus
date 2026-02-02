<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\List\ListItem;
use Crumbls\Tui\Extension\Core\Widget\ListWidget;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Widget\Corner;

test('simple', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, ListWidget::default()
        ->items(
            ListItem::new(Text::fromString('Hello')),
            ListItem::new(Text::fromString('World')),
        ));

    expect($buffer->toLines())->toEqual([
        'Hello',
        'World',
        '     ',
        '     ',
        '     ',
    ]);
});

test('start from BL corner', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, ListWidget::default()
        ->startCorner(Corner::BottomLeft)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '     ',
        '4    ',
        '3    ',
        '2    ',
        '1    ',
    ]);
});

test('highlight', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, ListWidget::default()
        ->startCorner(Corner::BottomLeft)
        ->select(1)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '     ',
        '  4  ',
        '  3  ',
        '>>2  ',
        '  1  ',
    ]);
});

test('with offset', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->offset(1)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '2  ',
        '3  ',
    ]);
});

test('with selected and offset', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->offset(1)
        ->select(2)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '  2',
        '>>3',
    ]);
});

test('scroll to selected if offset out of range', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->offset(0)
        ->select(3)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '  3',
        '>>4',
    ]);
});

test('with out of range negative offset', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->offset(-10)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '1  ',
        '2  ',
    ]);
});

test('with out of range positive offset', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->offset(100)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '4  ',
        '   ',
    ]);
});

test('with out of range positive selection', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->select(100)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '  3',
        '  4',
    ]);
});

test('with out of range negative selection', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    render($buffer, ListWidget::default()
        ->select(-100)
        ->items(
            ListItem::new(Text::fromString('1')),
            ListItem::new(Text::fromString('2')),
            ListItem::new(Text::fromString('3')),
            ListItem::new(Text::fromString('4')),
        ));

    expect($buffer->toLines())->toEqual([
        '>>1',
        '  2',
    ]);
});
