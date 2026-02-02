<?php

declare(strict_types=1);

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Backend\DummyBackend;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Display\Cell;
use Crumbls\Tui\Display\ClearType;
use Crumbls\Tui\Position\Position;

test('clear', function (): void {
    $backend = DummyBackend::fromDimensions(5, 5);
    $backend->draw(Buffer::filled(Area::fromDimensions(5, 5), Cell::fromChar('X'))->toUpdates());

    expect($backend->toString())->toEqual(implode("\n", [
        'XXXXX',
        'XXXXX',
        'XXXXX',
        'XXXXX',
        'XXXXX',
    ]));

    $backend->moveCursor(Position::at(2, 3));
    $backend->clearRegion(ClearType::AfterCursor);

    expect($backend->toString())->toEqual(implode("\n", [
        'XXXXX',
        'XXXXX',
        'XXXXX',
        '     ',
        'XXXXX',
    ]));
});
