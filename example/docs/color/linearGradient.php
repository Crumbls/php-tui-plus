<?php

declare(strict_types=1);

use Crumbls\Tui\Color\LinearGradient;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;
use Crumbls\Tui\Style\Style;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    GaugeWidget::default()
        ->ratio(1)
        ->style(
            Style::default()
                ->fg(
                    LinearGradient::from(RgbColor::fromHex('#ffaaaa'))
                        ->addStop(0.5, RgbColor::fromHex('#aaffaa'))
                        ->addStop(1, RgbColor::fromHex('#aaaaff'))
                )
        )
);
