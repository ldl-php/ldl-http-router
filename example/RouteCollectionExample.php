<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Router\Route\Collection\RouteCollection;
use LDL\Http\Router\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Route;

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

$routes = new RouteCollection([
    new Route(
        '/test/:name',
        new RouteDispatcherCollection([
            new MyDispatcher(),
        ]),
        'route 1'
    ),
    new Route(
        '/test/abc',
        new RouteDispatcherCollection([
            new MyDispatcher(),
        ]),
        'My static route'
    ),
    new RouteGroup('/frontend', [
        new Route(
            '/:name',
            new RouteDispatcherCollection([
                new MyDispatcher(),
            ]),
            'route 2'
        ),
        new Route(
            '/:name/:test',
            new RouteDispatcherCollection([
                new MyDispatcherTwo(),
            ]),
            'route 3'
        ),
    ]),
]);

foreach ($routes as $path => $r) {
    dump($path);
}

$matched = $routes->match('/test/abcd');

dd($matched->getResult()->getRoute()->getName());
