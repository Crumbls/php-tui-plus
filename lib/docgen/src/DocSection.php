<?php

declare(strict_types=1);

namespace Crumbls\Docgen;

final class DocSection
{
    public function __construct(
        public string $title,
        public string $description,
    ) {
    }
}
