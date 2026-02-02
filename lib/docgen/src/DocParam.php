<?php

declare(strict_types=1);

namespace Crumbls\Docgen;

final class DocParam
{
    public function __construct(
        public string $type,
        public string $name,
        public ?string $description
    ) {
    }
}
