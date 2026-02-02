<?php

declare(strict_types=1);

use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Display\BufferUpdate;
use Crumbls\Tui\Display\BufferUpdates;
use Crumbls\Tui\Display\Cell;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Span;

test('empty', function (): void {
    $buffer = Buffer::empty(Area::fromScalars(0, 0, 100, 100));

    expect($buffer)->toHaveCount(10000);
});

test('filled', function (): void {
    $cell = Cell::fromChar('X');
    $buffer = Buffer::filled(Area::fromScalars(0, 0, 10, 10), $cell);

    expect($buffer)->toHaveCount(100);
    expect($buffer->content())->toEqual(array_fill(0, 100, Cell::fromChar('X')));
    expect($buffer->get(Position::at(1, 1)))->not->toBe($cell, 'cells are propertly cloned!');
});

test('from lines', function (): void {
    $buffer = Buffer::fromLines([
        '1234',
        '12345678'
    ]);

    expect($buffer->toString())->toEqual("1234    \n12345678");
});

test('to string multi width', function (): void {
    $buffer = Buffer::fromLines(['🐈🐈']);

    expect($buffer->toLines())->toEqual(['🐈🐈']);
});

test('set style', function (): void {
    $buffer = Buffer::fromLines([
        '1234',
        '1234',
        '1234',
        '1234',
    ]);
    $buffer->setStyle(Area::fromScalars(1, 1, 2, 2), Style::default()->fg(AnsiColor::Red));

    expect($buffer->get(Position::at(0, 0))->fg)->toEqual(AnsiColor::Reset);
    expect($buffer->get(Position::at(1, 1))->fg)->toEqual(AnsiColor::Red);
    expect($buffer->get(Position::at(2, 2))->fg)->toEqual(AnsiColor::Red);
    expect($buffer->get(Position::at(3, 3))->fg)->toEqual(AnsiColor::Reset);
});

test('put line', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(4, 4));
    $buffer->putLine(Position::at(1, 1), Line::fromString('1234'), 2);

    expect($buffer->toLines())->toEqual([
        '    ',
        ' 12 ',
        '    ',
        '    ',
    ]);
});

test('put line many spans', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(14, 4));
    $buffer->putLine(Position::at(1, 1), Line::fromSpans(
        Span::fromString('one'),
        Span::fromString('😸'),
        Span::fromString('three'),
    ), 10);

    expect($buffer->toLines())->toEqual([
        '              ',
        ' one😸three   ',
        '              ',
        '              ',
    ]);
});

test('diff styles only', function (): void {
    $b1 = Buffer::fromLines(['a']);
    $b2 = Buffer::fromLines(['a']);

    expect($b1->diff($b2))->toHaveCount(0);

    $b2->get(Position::at(0, 0))->fg = AnsiColor::Red;

    expect($b1->diff($b2))->toHaveCount(1);
});

test('diff color value object', function (): void {
    $b1 = Buffer::fromLines(['a']);
    $b1->get(Position::at(0, 0))->fg = RgbColor::fromRgb(0, 0, 0);
    $b2 = Buffer::fromLines(['a']);
    $b2->get(Position::at(0, 0))->fg = RgbColor::fromRgb(0, 0, 0);

    expect($b1->diff($b2))->toHaveCount(0);
});

test('put string', function (): void {
    $b1 = Buffer::empty(Area::fromDimensions(5, 1));
    $b1->putString(Position::at(0, 0), '🐈234');

    // cat has width of 2 so should "occupy" 2 cells
    expect($b1->toChars())->toEqual(['🐈', ' ', '2', '3', '4']);
});

test('put string zero width', function (): void {
    $b1 = Buffer::empty(Area::fromDimensions(1, 1));
    $b1->putString(Position::at(0, 0), "\u{200B}a");

    // this is WRONG - but mb_strwidth returns 1 even for 0 width code points
    expect($b1->toChars())->toEqual(["\u{200B}"]);
});

test('diff no difference', function (): void {
    $b1 = Buffer::fromLines([
        '01234',
    ]);
    $b2 = Buffer::fromLines([
        '01234',
    ]);

    expect($b1->diff($b2))->toHaveCount(0);
});

test('diff last char diff', function (): void {
    $b1 = Buffer::fromLines([
        '01235',
    ]);
    $b2 = Buffer::fromLines([
        '01234',
    ]);

    $updates = $b1->diff($b2);

    expect($updates)->toHaveCount(1);
    expect($updates->at(0)->position->x)->toEqual(4);
    expect($updates->at(0)->position->y)->toEqual(0);
    expect($updates->at(0)->cell->char)->toEqual('4');
});

test('diff last char diff and second line', function (): void {
    $b1 = Buffer::fromLines([
        '01235',
        '00000',
    ]);
    $b2 = Buffer::fromLines([
        '01234',
        '01210',
    ]);

    expect($b1->diff($b2))->toHaveCount(4);
});

test('diff utf8', function (): void {
    $b1 = Buffer::fromLines([
        '🐈 😼',
        '00000',
    ]);
    $b2 = Buffer::fromLines([
        '🐈 🙀',
        '00000',
    ]);

    expect($b1->diff($b2))->toHaveCount(1);
});

test('diff multi width', function (): void {
    $b1 = Buffer::fromLines([
        '┌Title─┐  ',
        '└──────┘  ',
    ]);
    $b2 = Buffer::fromLines([
        '┌称号──┐  ',
        '└──────┘  ',
    ]);

    $updates = $b1->diff($b2);

    expect($updates)->toHaveCount(3);
    expect(iterator_to_array($updates))->toEqual([
        new BufferUpdate(Position::at(1, 0), Cell::fromChar('称')),
        new BufferUpdate(Position::at(3, 0), Cell::fromChar('号')),
        new BufferUpdate(Position::at(5, 0), Cell::fromChar('─')),
    ]);
});
