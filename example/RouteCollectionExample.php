<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$routes = require __DIR__.'/lib/example-routes.php';

use LDL\Router\Core\Router;

$router = new Router(require __DIR__.'/lib/example-routes.php');
$path = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '/test/my_name';
$matchedRoute = $router->dispatch($path);

if (null === $matchedRoute) {
    echo "No route matches path: $path\n";
    exit(1);
}

echo "Matched route: {$matchedRoute->getMatchedPath()->getResult()->getRoute()->getPath()}\n";

echo "Dispatch result:\n";
dump($matchedRoute->getResult());
