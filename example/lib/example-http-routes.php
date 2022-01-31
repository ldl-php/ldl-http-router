<?php

declare(strict_types=1);

use LDL\Router\Core\Route\Collection\RouteCollection;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Router\Http\Route\HttpRoute;

class HttpDispatcherExample implements RouteDispatcherInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $data;

    public function __construct(string $name, string $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function dispatch(string $name = null)
    {
        return $this->data;
    }
}

return new RouteCollection([
        new RouteCollection([
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
