<?php

declare(strict_types=1);

use Crumbls\Term\Event\CursorPositionEvent;
use Crumbls\Term\EventProvider\ArrayEventProvider;
use Crumbls\Term\RawMode\TestRawMode;
use Crumbls\Term\Terminal;
use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;

require 'vendor/autoload.php';

$title = '';

// -----
// ignore this! it is to enable this to work in "headless" mode for the tests.
$backend = PhpTermBackend::new(Terminal::new(
    eventProvider: ArrayEventProvider::fromEvents(new CursorPositionEvent(0, 0), new CursorPositionEvent(0, 0)),
    rawMode: new TestRawMode(),
));
// -----

$display = DisplayBuilder::default($backend)->inline(20)->build();
$widget = fn (string $title) => GridWidget::default()
        ->constraints(Constraint::length(20))

        ->widgets(
            BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString($title))
        );

for ($i = 0; $i < 5; $i++) {
    for ($ii = 0; $ii < 5; $ii++) {
        $display->draw($widget((string)$ii));
    }

    // insert _before_ the viewport, moving the cursor position down
    $display->insertBefore(20, $widget('done'));
}
$display->draw($widget('done'));
