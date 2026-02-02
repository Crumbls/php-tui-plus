<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\BorderType;
use Crumbls\Tui\Widget\HorizontalAlignment;

test('no borders, width=0, height=0', function (): void {
    $block = BlockWidget::default();

    expect($block->inner(Area::fromScalars(0, 0, 0, 0)))
        ->toEqual(Area::fromScalars(0, 0, 0, 0));
});

test('no borders, width=1, height=1', function (): void {
    $block = BlockWidget::default();

    expect($block->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(0, 0, 1, 1));
});

test('no borders, width=1, height=1, padding out of bounds', function (): void {
    $block = BlockWidget::default();

    expect($block->padding(Padding::all(10))->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(10, 10, 0, 0));
});

test('left, width=0', function (): void {
    $block = BlockWidget::default()->borders(Borders::LEFT);

    expect($block->inner(Area::fromScalars(0, 0, 0, 1)))
        ->toEqual(Area::fromScalars(0, 0, 0, 1));
});

test('left, width=1', function (): void {
    $block = BlockWidget::default()->borders(Borders::LEFT);

    expect($block->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(1, 0, 0, 1));
});

test('left, width=2', function (): void {
    $block = BlockWidget::default()->borders(Borders::LEFT);

    expect($block->inner(Area::fromScalars(0, 0, 2, 1)))
        ->toEqual(Area::fromScalars(1, 0, 1, 1));
});

test('top, height=0', function (): void {
    $block = BlockWidget::default()->borders(Borders::TOP);

    expect($block->inner(Area::fromScalars(0, 0, 1, 0)))
        ->toEqual(Area::fromScalars(0, 0, 1, 0));
});

test('left, height=1', function (): void {
    $block = BlockWidget::default()->borders(Borders::TOP);

    expect($block->inner(Area::fromScalars(0, 1, 1, 0)))
        ->toEqual(Area::fromScalars(0, 1, 1, 0));
});

test('left, height=2', function (): void {
    $block = BlockWidget::default()->borders(Borders::TOP);

    expect($block->inner(Area::fromScalars(0, 0, 1, 2)))
        ->toEqual(Area::fromScalars(0, 1, 1, 1));
});

test('right, width=0', function (): void {
    $block = BlockWidget::default()->borders(Borders::RIGHT);

    expect($block->inner(Area::fromScalars(0, 0, 0, 1)))
        ->toEqual(Area::fromScalars(0, 0, 0, 1));
});

test('right, width=1', function (): void {
    $block = BlockWidget::default()->borders(Borders::RIGHT);

    expect($block->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(0, 0, 0, 1));
});

test('right, width=2', function (): void {
    $block = BlockWidget::default()->borders(Borders::RIGHT);

    expect($block->inner(Area::fromScalars(0, 0, 2, 1)))
        ->toEqual(Area::fromScalars(0, 0, 1, 1));
});

test('bottom, height=0', function (): void {
    $block = BlockWidget::default()->borders(Borders::BOTTOM);

    expect($block->inner(Area::fromScalars(0, 0, 1, 0)))
        ->toEqual(Area::fromScalars(0, 0, 1, 0));
});

test('bottom, height=1', function (): void {
    $block = BlockWidget::default()->borders(Borders::BOTTOM);

    expect($block->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(0, 0, 1, 0));
});

test('bottom, height=2', function (): void {
    $block = BlockWidget::default()->borders(Borders::BOTTOM);

    expect($block->inner(Area::fromScalars(0, 0, 1, 2)))
        ->toEqual(Area::fromScalars(0, 0, 1, 1));
});

test('all borders, 0x0', function (): void {
    $block = BlockWidget::default()->borders(Borders::ALL);

    expect($block->inner(Area::fromScalars(0, 0, 0, 0)))
        ->toEqual(Area::fromScalars(0, 0, 0, 0));
});

test('all borders, 1x1', function (): void {
    $block = BlockWidget::default()->borders(Borders::ALL);

    expect($block->inner(Area::fromScalars(0, 0, 1, 1)))
        ->toEqual(Area::fromScalars(1, 1, 0, 0));
});

test('all borders, 2x2', function (): void {
    $block = BlockWidget::default()->borders(Borders::ALL);

    expect($block->inner(Area::fromScalars(0, 0, 2, 2)))
        ->toEqual(Area::fromScalars(1, 1, 0, 0));
});

test('all borders, 3x3', function (): void {
    $block = BlockWidget::default()->borders(Borders::ALL);

    expect($block->inner(Area::fromScalars(0, 0, 3, 3)))
        ->toEqual(Area::fromScalars(1, 1, 1, 1));
});

test('inner takes into account the title', function (): void {
    $block = BlockWidget::default()->titles(Title::fromString('Hello World'));

    expect($block->inner(Area::fromScalars(0, 0, 0, 1)))
        ->toEqual(Area::fromScalars(0, 1, 0, 0));
});

test('title alignment right', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 1));
    render(
        $buffer,
        BlockWidget::default()->titles(Title::fromString('test')->horizontalAlignmnet(HorizontalAlignment::Right))
    );

    expect($buffer->toString())->toEqual('    test');
});

test('title alignment left', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 1));
    render(
        $buffer,
        BlockWidget::default()->titles(Title::fromString('test')->horizontalAlignmnet(HorizontalAlignment::Left))
    );

    expect($buffer->toString())->toEqual('test    ');
});

test('title alignment center', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 1));
    render(
        $buffer,
        BlockWidget::default()->titles(Title::fromString('test')->horizontalAlignmnet(HorizontalAlignment::Center))
    );

    expect($buffer->toString())->toEqual('  test  ');
});

test('renders borders', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, BlockWidget::default()->borders(Borders::ALL));

    expect($buffer->toLines())->toEqual([
        '┌───┐',
        '│   │',
        '│   │',
        '│   │',
        '└───┘',
    ]);
});

test('renders borders rounded', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, BlockWidget::default()->borderType(BorderType::Rounded)->borders(Borders::ALL));

    expect($buffer->toLines())->toEqual([
        '╭───╮',
        '│   │',
        '│   │',
        '│   │',
        '╰───╯',
    ]);
});

test('render with vertical borders', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, BlockWidget::default()->borders(Borders::VERTICAL));

    expect($buffer->toLines())->toEqual([
        '─────',
        '     ',
        '     ',
        '     ',
        '─────',
    ]);
});

test('render with horizontal borders', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 5));
    render($buffer, BlockWidget::default()->borders(Borders::HORIZONTAL));

    expect($buffer->toLines())->toEqual([
        '│   │',
        '│   │',
        '│   │',
        '│   │',
        '│   │',
    ]);
});

test('renders with title', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 5));
    render(
        $buffer,
        BlockWidget::default()
            ->borderType(BorderType::Rounded)
            ->borders(Borders::ALL)
            ->titles(Title::fromString('G\'day')->horizontalAlignmnet(HorizontalAlignment::Left))
    );

    expect($buffer->toLines())->toEqual([
        "╭G'day─╮",
        '│      │',
        '│      │',
        '│      │',
        '╰──────╯',
    ]);
});

test('renders with padding', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 5));
    $block = BlockWidget::default()
        ->borderType(BorderType::Rounded)
        ->borders(Borders::ALL)
        ->widget(ParagraphWidget::fromText(Text::fromString('Foob')))
        ->padding(Padding::fromScalars(1, 1, 1, 1));

    render($buffer, $block);

    expect($buffer->toLines())->toEqual([
        '╭──────╮',
        '│      │',
        '│ Foob │',
        '│      │',
        '╰──────╯',
    ]);
});

test('bottom border only', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    $block = BlockWidget::default()
        ->borders(Borders::BOTTOM);

    render($buffer, $block);

    expect($buffer->toLines())->toEqual([
        '   ',
        '───',
    ]);
});

test('top border only', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    $block = BlockWidget::default()
        ->borders(Borders::TOP);

    render($buffer, $block);

    expect($buffer->toLines())->toEqual([
        '───',
        '   ',
    ]);
});

test('left border only', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    $block = BlockWidget::default()
        ->borders(Borders::LEFT);

    render($buffer, $block);

    expect($buffer->toLines())->toEqual([
        '│  ',
        '│  ',
    ]);
});

test('right border only', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(3, 2));
    $block = BlockWidget::default()
        ->borders(Borders::RIGHT);

    render($buffer, $block);

    expect($buffer->toLines())->toEqual([
        '  │',
        '  │',
    ]);
});
