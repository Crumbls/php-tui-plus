<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;

test('invalid range', function (): void {
    GaugeWidget::default()->ratio(2.5);
})->throws(RuntimeException::class, 'Gauge ratio must be between 0 and 1 got 2.500000');
