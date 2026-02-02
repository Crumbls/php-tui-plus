<?php

declare(strict_types=1);

use PhpTui\Term\Action;
use PhpTui\Term\Actions;
use PhpTui\Term\ClearType as PhpTuiClearType;
use PhpTui\Term\Event\CursorPositionEvent;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PhpTui\Term\Painter\ArrayPainter;
use PhpTui\Term\RawMode\TestRawMode;
use PhpTui\Term\Terminal;
use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Display\BufferUpdate;
use Crumbls\Tui\Display\BufferUpdates;
use Crumbls\Tui\Display\Cell;
use Crumbls\Tui\Display\ClearType;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;

function drawBuffer(ArrayPainter $buffer, BufferUpdates $updates): void
{
    $backend = new PhpTermBackend(Terminal::new($buffer));
    $backend->draw($updates);
    $backend->flush();
}

test('disable raw mode after getting cursor position', function (): void {
    $buffer = ArrayPainter::new();
    $rawMode = new TestRawMode();
    $provider = ArrayEventProvider::fromEvents(
        new CursorPositionEvent(10, 10)
    );

    $backend = new PhpTermBackend(Terminal::new(
        $buffer,
        rawMode: $rawMode,
        eventProvider: $provider
    ));
    $position = $backend->cursorPosition();
    expect($buffer->actions())->toEqual([
        Actions::requestCursorPosition()
    ]);
    expect($position)->toEqual(Position::at(10, 10));
    expect($rawMode->isEnabled())->toBeFalse();
});

test('disable raw mode if cursor position cannot be determined', function (): void {
    $buffer = ArrayPainter::new();
    $rawMode = new TestRawMode();
    $backend = new PhpTermBackend(Terminal::new(
        $buffer,
        rawMode: $rawMode,
    ), blockingTimeout: 0);

    try {
        $backend->cursorPosition();
        throw new \Exception('Exception not thrown');
    } catch (RuntimeException) {
    }
    expect($rawMode->isEnabled())->toBeFalse();
});

test('move cursor', function (): void {
    $buffer = ArrayPainter::new();
    $backend = new PhpTermBackend(Terminal::new($buffer));
    $backend->moveCursor(Position::at(1, 2));
    expect($buffer->actions())->toEqual([
        Actions::moveCursor(2, 1)
    ]);
});

test('clear all', function (): void {
    $buffer = ArrayPainter::new();
    $backend = new PhpTermBackend(Terminal::new($buffer));
    $backend->clearRegion(ClearType::ALL);
    expect($buffer->actions())->toEqual([
        Actions::clear(PhpTuiClearType::All)
    ]);
});

test('clear after cursor', function (): void {
    $buffer = ArrayPainter::new();
    $backend = new PhpTermBackend(Terminal::new($buffer));
    $backend->clearRegion(ClearType::AfterCursor);
    expect($buffer->actions())->toEqual([
        Actions::clear(PhpTuiClearType::FromCursorDown)
    ]);
});

test('diagonal line', function (): void {
    $buffer = ArrayPainter::new();
    drawBuffer($buffer, new BufferUpdates([
        new BufferUpdate(
            Position::at(0, 0),
            Cell::fromChar('X')->setStyle(Style::default()->fg(AnsiColor::Red)),
        ),
        new BufferUpdate(
            Position::at(1, 1),
            Cell::fromChar('X'),
        ),
        new BufferUpdate(
            Position::at(2, 2),
            Cell::fromChar('X'),
        ),
    ]));
    expect(array_map(static fn (Action $action): string => $action->__toString(), $buffer->actions()))->toBe([
        'MoveCursor(line=1,col=1)',
        'SetForegroundColor(Red)',
        'Print("X")',
        'MoveCursor(line=2,col=2)',
        'SetForegroundColor(Reset)',
        'Print("X")',
        'MoveCursor(line=3,col=3)',
        'Print("X")',
        'SetForegroundColor(Reset)',
        'SetBackgroundColor(Reset)',
        'Reset()',
    ]);
});

test('does not move cursor unnecessarily', function (): void {
    $buffer = ArrayPainter::new();
    drawBuffer($buffer, new BufferUpdates([
        new BufferUpdate(
            Position::at(0, 0),
            Cell::fromChar('X')->setStyle(Style::default()->fg(AnsiColor::Red)),
        ),
        new BufferUpdate(
            Position::at(1, 0),
            Cell::fromChar('X'),
        ),
        new BufferUpdate(
            Position::at(2, 0),
            Cell::fromChar('X'),
        ),
    ]));
    expect(array_map(static fn (Action $action): string => $action->__toString(), $buffer->actions()))->toBe([
        'MoveCursor(line=1,col=1)',
        'SetForegroundColor(Red)',
        'Print("X")',
        'SetForegroundColor(Reset)',
        'Print("X")',
        'Print("X")',
        'SetForegroundColor(Reset)',
        'SetBackgroundColor(Reset)',
        'Reset()',
    ]);
});

test('does not change color unnecessarily', function (): void {
    $buffer = ArrayPainter::new();
    drawBuffer($buffer, new BufferUpdates([
        new BufferUpdate(
            Position::at(0, 0),
            Cell::fromChar('X')->setStyle(Style::default()->fg(RgbColor::fromRgb(0, 0, 0))->bg(RgbColor::fromRgb(0, 0, 0))),
        ),
        new BufferUpdate(
            Position::at(1, 0),
            Cell::fromChar('X')->setStyle(Style::default()->fg(RgbColor::fromRgb(0, 0, 0))->bg(RgbColor::fromRgb(0, 0, 0))),
        ),
    ]));
    expect(array_map(static fn (Action $action): string => $action->__toString(), $buffer->actions()))->toBe([
        'MoveCursor(line=1,col=1)',
        'SetRgbForegroundColor(0, 0, 0)',
        'SetRgbBackgroundColor(0, 0, 0)',
        'Print("X")',
        'Print("X")',
        'SetForegroundColor(Reset)',
        'SetBackgroundColor(Reset)',
        'Reset()',
    ]);
});

test('modifiers reset', function (): void {
    $buffer = ArrayPainter::new();
    drawBuffer($buffer, new BufferUpdates([
        new BufferUpdate(
            Position::at(0, 0),
            Cell::fromChar('X')->setStyle(
                Style::default()
                ->addModifier(Modifier::ITALIC)
                ->addModifier(Modifier::BOLD)
                ->addModifier(Modifier::REVERSED)
                ->addModifier(Modifier::DIM)
                ->addModifier(Modifier::HIDDEN)
                ->addModifier(Modifier::SLOWBLINK)
                ->addModifier(Modifier::UNDERLINED)
                ->addModifier(Modifier::RAPIDBLINK)
                ->addModifier(Modifier::CROSSEDOUT)
            ),
        ),
        new BufferUpdate(
            Position::at(1, 0),
            Cell::fromChar('X')->setStyle(Style::default()),
        ),
    ]));
    expect(array_map(static fn (Action $action): string => $action->__toString(), $buffer->actions()))->toBe([
        'MoveCursor(line=1,col=1)',
        'SetModifier(Italic,on)',
        'SetModifier(Bold,on)',
        'SetModifier(Reverse,on)',
        'SetModifier(Dim,on)',
        'SetModifier(Hidden,on)',
        'SetModifier(SlowBlink,on)',
        'SetModifier(Underline,on)',
        'SetModifier(RapidBlink,on)',
        'SetModifier(Strike,on)',
        'Print("X")',
        'SetModifier(Italic,off)',
        'SetModifier(Bold,off)',
        'SetModifier(Reverse,off)',
        'SetModifier(Dim,off)',
        'SetModifier(Hidden,off)',
        'SetModifier(SlowBlink,off)',
        'SetModifier(Underline,off)',
        'SetModifier(RapidBlink,off)',
        'SetModifier(Strike,off)',
        'Print("X")',
        'SetForegroundColor(Reset)',
        'SetBackgroundColor(Reset)',
        'Reset()',
    ]);
});
