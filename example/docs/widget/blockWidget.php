<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\BorderType;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    BlockWidget::default()
        ->borders(Borders::ALL)
        ->titles(Title::fromString('Hello World'))
        ->borderType(BorderType::Rounded)
        ->widget(ParagraphWidget::fromText(Text::fromString('This is a block example')))
);
