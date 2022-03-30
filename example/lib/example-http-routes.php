<?php

declare(strict_types=1);

use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Http\Route\Collection\HttpRouteCollection;
use LDL\Router\Http\Route\HttpRoute;

require_once __DIR__.'/example-http-dispatchers.php';

return new HttpRouteCollection([
        new HttpRouteCollection([
            new HttpRoute(
                '/static',
                ['GET'],
                new RouteDispatcherCollection([
                    new HttpDispatcherExample('GET dispatcher', 'GET Result'),
                ]),
                'GET route'
            ),
            new HttpRoute(
                '/:name',
                ['POST'],
                new RouteDispatcherCollection([
                    new HttpDispatcherExample('POST dispatcher', 'POST Result'),
                ]),
                'POST route'
            ),
            new HttpRoute(
                '/:name/:test',
                ['POST', 'GET'],
                new RouteDispatcherCollection([
                    new HttpDispatcherExample('POST/GET dispatcher', 'POST/GET Result'),
                ]),
                'POST/GET Route'
            ),
        ], '/group'),
    ], '/frontend');
