<?php

declare(strict_types=1);

namespace Crumbls\Tui\Tests\Benchmark\Extension\ImageMagick;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpTui\Term\InformationProvider\AggregateInformationProvider;
use PhpTui\Term\InformationProvider\ClosureInformationProvider;
use PhpTui\Term\Painter\StringPainter;
use PhpTui\Term\RawMode\TestRawMode;
use PhpTui\Term\Terminal;
use PhpTui\Term\TerminalInformation\Size;
use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\Display\Display;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImageShape;

#[Iterations(4)]
#[Revs(25)]
final class ImageShapeBench
{
    private readonly Display $display;

    private readonly StringPainter $painter;

    public function __construct()
    {
        $this->painter = new StringPainter();
        $terminal = Terminal::new(
            infoProvider: new AggregateInformationProvider([
                ClosureInformationProvider::new(static function (string $info) {
                    if ($info === Size::class) {
                        return new Size(100, 100);
                    }
                })

            ]),
            rawMode: new TestRawMode(),
            painter: $this->painter,
        );
        $this->display = DisplayBuilder::default(PhpTermBackend::new($terminal))->build();
    }

    public function benchImageShape(): void
    {
        $this->display->draw(
            CanvasWidget::fromIntBounds(0, 320, 0, 200)->draw(
                ImageShape::fromPath(
                    __DIR__ . '/../../../Unit/Extension/ImageMagick/Shape/example.jpg',
                )
            )
        );
    }
}
