<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Core\Router;

$router = new Router(
    require __DIR__.'/lib/example-routes.php'
);

var_dump($router->getRouteList());
