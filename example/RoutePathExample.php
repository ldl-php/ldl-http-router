<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Router\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Path\RoutePathParser;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;

$path = new RoutePathParser();

$result = $path->match('/frontend/TEST', '/frontend/[A-Z]+:name');

class MyDispatcher implements RouteDispatcherInterface
{
    public function dispatch()
    {
        return 'abc';
    }
}

class MyDispatcherTwo implements RouteDispatcherInterface
{
    public function dispatch()
    {
        return 'def';
    }
}

$routes = [
    new Route(
        '/test/:name',
        new RouteDispatcherCollection([
            new MyDispatcher(),
        ])
    ),
    new RouteGroup('/frontend', [
        new Route(
            '/:name',
            new RouteDispatcherCollection([
                new MyDispatcher(),
            ])
        ),
        new Route(
            '/:name/:test',
            new RouteDispatcherCollection([
                new MyDispatcherTwo(),
            ])
        ),
    ]),
];

$router = new Router($routes);

$router->dispatch('/frontend/TEST/abc');
