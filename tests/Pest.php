<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\AggregateShapePainter;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\CoreExtension;
use Crumbls\Tui\Extension\Core\Widget\CanvasRenderer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;
use Crumbls\Tui\Widget\WidgetRenderer\AggregateWidgetRenderer;
use Crumbls\Tui\Widget\WidgetRenderer\NullWidgetRenderer;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getWidgetRenderer(): WidgetRenderer
{
    $coreExtension = new CoreExtension();

    return new AggregateWidgetRenderer([
        new CanvasRenderer(
            new AggregateShapePainter($coreExtension->shapePainters())
        ),
        ...$coreExtension->widgetRenderers()
    ]);
}

function render(Buffer $buffer, Widget $widget): void
{
    getWidgetRenderer()->render(
        new NullWidgetRenderer(),
        $widget,
        $buffer,
        $buffer->area(),
    );
}

/**
 * @return string[]
 * @param int<0,max> $width
 * @param int<0,max> $height
 */
function renderToLines(Widget $widget, int $width = 8, int $height = 5): array
{
    $area = Area::fromDimensions($width, $height);
    $buffer = Buffer::empty($area);
    getWidgetRenderer()->render(new NullWidgetRenderer(), $widget, $buffer, $buffer->area());

    return $buffer->toLines();
}
