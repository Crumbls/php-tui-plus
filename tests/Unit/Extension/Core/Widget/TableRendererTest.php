<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Table\TableCell;
use Crumbls\Tui\Extension\Core\Widget\Table\TableRow;
use Crumbls\Tui\Extension\Core\Widget\TableWidget;
use Crumbls\Tui\Layout\Constraint;

test('not enough rows', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, TableWidget::default()
        ->header(TableRow::fromCells(
            TableCell::fromString('Ones'),
            TableCell::fromString('Twos'),
        ))
        ->widths(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->rows(
            TableRow::fromCells(
                TableCell::fromString('1'),
                TableCell::fromString('2'),
            ),
        ));

    expect($buffer->toLines())->toEqual([
        'Ones Twos ',
        '1    2    ',
        '          ',
        '          ',
    ]);
});

test('no widths', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, TableWidget::default()
        ->header(TableRow::fromCells(
            TableCell::fromString('Ones'),
            TableCell::fromString('Twos'),
        ))
        ->rows(
            TableRow::fromCells(
                TableCell::fromString('1'),
                TableCell::fromString('2'),
            ),
        ));

    expect($buffer->toLines())->toEqual([
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});

test('select', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, TableWidget::default()
        ->select(0)
        ->offset(0)
        ->header(TableRow::fromCells(
            TableCell::fromString('Ones'),
            TableCell::fromString('Twos'),
        ))
        ->widths(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->rows(
            TableRow::fromCells(
                TableCell::fromString('1'),
                TableCell::fromString('2'),
            ),
            TableRow::fromCells(
                TableCell::fromString('1-1'),
                TableCell::fromString('2-2'),
            ),
        ));

    expect($buffer->toLines())->toEqual([
        '  Ones Two',
        '>>1    2  ',
        '  1-1  2-2',
        '          ',
    ]);
});

test('table with header and two rows', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, TableWidget::default()
        ->header(TableRow::fromCells(
            TableCell::fromString('Ones'),
            TableCell::fromString('Twos'),
        ))
        ->widths(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->rows(
            TableRow::fromCells(
                TableCell::fromString('1'),
                TableCell::fromString('2'),
            ),
            TableRow::fromCells(
                TableCell::fromString('1-1'),
                TableCell::fromString('2-2'),
            ),
        ));

    expect($buffer->toLines())->toEqual([
        'Ones Twos ',
        '1    2    ',
        '1-1  2-2  ',
        '          ',
    ]);
});

test('offset out of range', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 4));
    render($buffer, TableWidget::default()
        ->offset(5)
        ->rows(
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
            TableRow::fromCells(TableCell::fromString('1'), TableCell::fromString('2')),
        ));

    expect($buffer->toLines())->toEqual([
        '          ',
        '          ',
        '          ',
        '          ',
    ]);
});
