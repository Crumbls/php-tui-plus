<?php

declare(strict_types=1);

namespace Crumbls\Docgen;

enum DocExampleType
{
    case CodeAndOutput;
    case CodeOnly;
    case None;

}
