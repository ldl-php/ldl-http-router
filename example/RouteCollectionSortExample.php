<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Core\Route\Path\RoutePathParser;
$parse = new RoutePathParser();

$parse->sort(require __DIR__.'/lib/example-routes.php');
