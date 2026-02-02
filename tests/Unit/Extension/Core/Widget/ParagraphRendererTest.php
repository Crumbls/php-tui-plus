<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\Paragraph\Wrap;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Widget\HorizontalAlignment;

test('fromString', function (): void {
    $paragraph = ParagraphWidget::fromString('Hello');
    expect($paragraph)->toEqual(ParagraphWidget::fromText(Text::fromString('Hello')));
});

test('fromMultilineString', function (): void {
    $paragraph = ParagraphWidget::fromString("Hello\nGoodbye");
    expect($paragraph)->toEqual(ParagraphWidget::fromLines(
        Line::fromString('Hello'),
        Line::fromString('Goodbye'),
    ));
    $area = Area::fromScalars(0, 0, 10, 2);
    $buffer = Buffer::empty($area);
    render($buffer, $paragraph);
    expect($buffer->toLines())->toEqual([
        'Hello     ',
        'Goodbye   ',
    ]);
});

test('simple', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 1));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Gday')
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual('Gday    ');
});

test('wrap', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Gday mate lets put another shrimp on the barby')
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Gday mat',
        'e lets p',
        'ut anoth',
    ]));
});

test('align right', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 1));
    render($buffer, ParagraphWidget::fromText(
        Text::fromLine(
            Line::fromString('Gday')->alignment(HorizontalAlignment::Right)
        )
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual('    Gday');
});

test('align left and right', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 1));
    render($buffer, ParagraphWidget::fromLines(
        Line::fromString('1/1')->alignment(HorizontalAlignment::Left),
        Line::fromString('About')->alignment(HorizontalAlignment::Right),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual('1/1       ');
});

test('line with unicode wrapped by character', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(14, 1));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('こんにちは, 世界! 😃')
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual('こんにちは, 世');
});

test('line with lorem ipsum wrapped by character', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(18, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Lorem ipsum dolor sit amet, consectetur')
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Lorem ipsum dolor ',
        'sit amet, consecte',
        'tur               ',
    ]));
});

test('line with lorem ipsum wrapped by word', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(18, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Lorem ipsum dolor sit amet, consectetur')
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Lorem ipsum dolor ',
        'sit amet,         ',
        'consectetur       ',
    ]));
});

test('line with hello wrapped by word', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Hello Goodbye')
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Hello     ',
        'Goodbye   ',
    ]));
});

test('line with hello wrapped by character', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Hello Goodbye')
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Hello Good',
        'bye       ',
    ]));
});

test('line with welcome to the PHP-TUI 1', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(15, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Welcome to the PHP-TUI 🐘 application.'),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Welcome to the ',
        'PHP-TUI 🐘     ',
        'application.   ',
    ]));
});

test('line with welcome to the PHP-TUI 2', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(8, 4));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('Welcome to the PHP-TUI 🐘'),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'Welcome ',
        'to the  ',
        'PHP-TUI ',
        '🐘      ',
    ]));
});

test('line with multiple a letters preserving spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('AAAAAAAAAAAAAAAAAAAA    AAA'),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'AAAAAAAAAAAAAAAAAAAA',
        '   AAA              ',
    ]));
});

test('line with multiple a letters while not preserving spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('AAAAAAAAAAAAAAAAAAAA    AAA'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'AAAAAAAAAAAAAAAAAAAA',
        'AAA                 ',
    ]));
});

test('line with multiple words wrapped by character', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij klmnopabcd efgh ijklmnopabcdefg hijkl mnopab c d e f g h i j k l m n o'),
    )->wrap(Wrap::Character));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcd efghij klmnopab',
        'cd efgh ijklmnopabcd',
        'efg hijkl mnopab c d',
        ' e f g h i j k l m n',
        ' o                  ',
    ]));
});

test('line with multiple words and single spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij klmnopabcd efgh ijklmnopabcdefg hijkl mnopab c d e f g h i j k l m n o'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcd efghij         ',
        'klmnopabcd efgh     ',
        'ijklmnopabcdefg     ',
        'hijkl mnopab c d e f',
        'g h i j k l m n o   ',
    ]));
});

test('line with multiple words and multiple spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij    klmnopabcd efgh     ijklmnopabcdefg hijkl mnopab c d e f g h i j k l m n o'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcd efghij         ',
        'klmnopabcd efgh     ',
        'ijklmnopabcdefg     ',
        'hijkl mnopab c d e f',
        'g h i j k l m n o   ',
    ]));
});

test('line with short words', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(4, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcd',
        'efgh',
        'ij  ',
    ]));
});

test('line with with single space', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('hello world'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'hello',
        'world',
    ]));
});

test('line with with multiple spaces by word', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 7));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('hello                           world '),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        'hello',
        '     ',
        '     ',
        '     ',
        '     ',
        '  wor',
        'ld   ',
    ]));
});

test('line with with multiple spaces by word trimmed', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(5, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('hello                           world '),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'hello',
        '     ',
        'world',
    ]));
});

test('line with max line width 1', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(1, 1));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij klmnopabcd efgh ijklmnopabcdefg hijkl mnopab '),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'a',
    ]));
});

test('line with max line width 1 with double width characters', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(1, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString("コンピュータ上で文字を扱う場合、典型的には文字\naaa\naによる通信を行う場合にその両端点では、"),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        ' ',
        'a',
        'a',
        'a',
        'a',
    ]));
});

test('line with single char with multiples spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 2));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('a                                                                     '),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'a                   ',
        '                    ',
    ]));
});

test('line with short lines', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 7));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString("abcdefg\nhijklmno\npabcdefg\nhijklmn\nopabcdefghijk\nlmnopabcd\n\n\nefghijklmno"),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcdefg             ',
        'hijklmno            ',
        'pabcdefg            ',
        'hijklmn             ',
        'opabcdefghijk       ',
        'lmnopabcd           ',
        'efghijklmno         ',
    ]));
});

test('line with long word', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 4));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcdefghijklmnopabcdefghijklmnopabcdefghijklmnopabcdefghijklmno'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcdefghijklmnopabcd',
        'efghijklmnopabcdefgh',
        'ijklmnopabcdefghijkl',
        'mno                 ',
    ]));
});

test('line with mixed length words', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('abcd efghij klmnopabcdefghijklmnopabcdefghijkl mnopab cdefghi j klmno'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'abcd efghij         ',
        'klmnopabcdefghijklmn',
        'opabcdefghijkl      ',
        'mnopab cdefghi j    ',
        'klmno               ',
    ]));
});

test('line with double width chars', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 5));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('コンピュータ上で文字を扱う場合、典型的には文字による通信を行う場合にその両端点では、'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'コンピュータ上で文字',
        'を扱う場合、典型的に',
        'は文字による通信を行',
        'う場合にその両端点で',
        'は、                ',
    ]));
});

test('line with double width chars with spaces', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(20, 6));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString('コンピュ ータ上で文字を扱う場合、 典型的には文 字による 通信を行 う場合にその両端点では、'),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        'コンピュ            ',
        'ータ上で文字を扱う場',
        '合、 典型的には文   ',
        '字による 通信を行   ',
        'う場合にその両端点で',
        'は、                ',
    ]));
});

test('line with indentation preserved', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 6));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString("               4 Indent\n                 must wrap!"),
    )->wrap(Wrap::Word));
    expect($buffer->toString())->toEqual(implode("\n", [
        '          ',
        '    4     ',
        'Indent    ',
        '          ',
        '      must',
        'wrap!     ',
    ]));
});

test('line with indentation not preserved', function (): void {
    $buffer = Buffer::empty(Area::fromDimensions(10, 3));
    render($buffer, ParagraphWidget::fromText(
        Text::fromString("               4 Indent\n                 must wrap!"),
    )->wrap(Wrap::WordTrimmed));
    expect($buffer->toString())->toEqual(implode("\n", [
        '4 Indent  ',
        '          ',
        'must wrap!',
    ]));
});
