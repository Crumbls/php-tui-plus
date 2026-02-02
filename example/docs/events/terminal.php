<?php

declare(strict_types=1);

use Crumbls\Term\Terminal;
use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\DisplayBuilder;

require 'vendor/autoload.php';

$terminal = Terminal::new();
$display = DisplayBuilder::default(PhpTermBackend::new($terminal))->build();
