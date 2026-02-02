<?php

declare(strict_types=1);

use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\ImageMagick\ImageMagickExtension;
use Crumbls\Tui\Extension\ImageMagick\Widget\ImageWidget;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()
    ->addExtension(new ImageMagickExtension())
    ->build();
$display->draw(
    new ImageWidget(path: __DIR__ . '/../shape/example.jpg'),
);
