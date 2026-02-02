<?php

declare(strict_types=1);

namespace Crumbls\Docgen\Renderer;

use Crumbls\Docgen\DocRenderer;
use Crumbls\Docgen\DocSection;

final class DocSectionRenderer implements DocRenderer
{
    public function render(DocRenderer $renderer, object $object): ?string
    {
        if (!$object instanceof DocSection) {
            return null;
        }

        $out = [];
        $out[] = '---';
        $out[] = 'bookCollapseSection: true';
        $out[] = sprintf('title: %s', $object->title);
        $out[] = sprintf('description: %s', $object->description);
        $out[] = '---';
        $out[] = $object->description;

        return implode("\n", $out);
    }
}
