<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Route;

$g = new RouteGroup('/:param', [
    new Route('/:name', []),
]);

$g->matches('/abc/def');

dd($g->getMatchedRoute()->getPath()->getParameters());
