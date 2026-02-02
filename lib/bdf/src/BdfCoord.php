<?php

declare(strict_types=1);

namespace Crumbls\BDF;

final class BdfCoord
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) {
    }
}
