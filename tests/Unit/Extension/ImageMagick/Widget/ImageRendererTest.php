<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Backend\DummyBackend;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\ImageMagick\ImageMagickExtension;
use Crumbls\Tui\Extension\ImageMagick\Widget\ImageWidget;

test('image widget', function (): void {
    if (!extension_loaded('imagick')) {
        $this->markTestSkipped('imagick extension not loaded');
    }
    $backend = new DummyBackend(10, 4);
    $display = DisplayBuilder::default($backend)->addExtension(new ImageMagickExtension())->build();
    $display->draw(
        new ImageWidget(__DIR__ . '/../Shape/example.jpg', marker: Marker::Block),
    );

    expect(str_replace("\n", '', $backend->toString()))->toBe(str_repeat('█', 40));
});
