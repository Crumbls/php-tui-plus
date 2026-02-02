<?php

declare(strict_types=1);

namespace Crumbls\Docgen;

interface DocRenderer
{
    public function render(DocRenderer $renderer, object $object): ?string;
}
