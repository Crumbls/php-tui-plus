<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Shape\MapResolution;
use Crumbls\Tui\Extension\Core\Shape\MapShape;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\BorderType;
use Crumbls\Tui\Widget\Direction;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->clear();
$display->draw(
    GridWidget::default()
        ->direction(Direction::Horizontal)
        ->constraints(
            Constraint::percentage(50),
            Constraint::percentage(50)
        )
        ->widgets(
            BlockWidget::default()
                ->titles(Title::fromString('Left'))
                ->padding(Padding::all(2))
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->widget(
                    CanvasWidget::fromIntBounds(-180, 180, -90, 90)
                        ->draw(
                            MapShape::default()->resolution(MapResolution::High)
                        ),
                ),
            BlockWidget::default()
                ->titles(Title::fromString('Right'))
                ->padding(Padding::all(2))
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->widget(
                    ParagraphWidget::fromText(
                        Text::parse(<<<'EOT'
                            The <fg=green>world</> is the totality of <options=bold>entities</>,
                            the whole of reality, or everything that is.[1] The nature of the
                            world has been <fg=red>conceptualized</> differently in different fields. Some
                            conceptions see the world as unique while others talk of a
                            plurality of <bg=green>worlds</>.
                            EOT)
                    )
                ),
        )
);
