<?php

declare(strict_types=1);

use LDL\Router\Core\Route\Collection\RouteCollection;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Dispatcher\NeedsDispatchersInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Router\Core\Route\Route;

class MyDispatcher implements RouteDispatcherInterface
{
    public function getName(): string
    {
        return 'dispatcher 1';
    }

    public function dispatch(string $name = null)
    {
        dump($name);

        return 'abc';
    }
}

class MyDispatcherTwo implements NeedsDispatchersInterface
{
    public function getName(): string
    {
        return 'dispatcher 2';
    }

    public function dispatch(RouteDispatcherResultCollectionInterface $results = null, string ...$params)
    {
        return [
            'dispatcher 2' => $results->findByDispatcherName('dispatcher 1')->getDispatcherResult(),
        ];
    }
}

return new RouteCollection([
    new RouteCollection([
        new Route(
            '/:name',
            new RouteDispatcherCollection([
                static function (RouteDispatcherResultCollectionInterface $dispatchers, string ...$params): array {
                    return ['from callable'];
                },
            ]),
            'route 2'
        ),
        new Route(
            '/:name/:test',
            new RouteDispatcherCollection([
                new MyDispatcher(),
                new MyDispatcherTwo(),
            ]),
            'route 3'
        ),
    ], '/frontend-group'),
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
]);
