<?php

declare(strict_types=1);

namespace Crumbls\BDF;

final class BdfSize
{
    public function __construct(
        public readonly int $width,
        public readonly int $height
    ) {
    }
}
