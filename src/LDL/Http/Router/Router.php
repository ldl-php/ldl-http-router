<?php

declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Router\Route\Collection\RouteCollection;
use LDL\Http\Router\Route\Collection\RouteCollectionInterface;
use LDL\Http\Router\Route\Path\RoutePathParser;
use LDL\Http\Router\Route\Path\RoutePathParserInterface;
use LDL\Http\Router\Route\RouteInterface;

class Router implements RouterInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var RoutePathParserInterface
     */
    private $pathMatchingAlgorithm;

    public function __construct(
        iterable $routes,
        RoutePathParserInterface $pathMatchingAlgorithm = null
    ) {
        $this->pathMatchingAlgorithm = $pathMatchingAlgorithm ?? new RoutePathParser('/');
        $this->routes = new RouteCollection($this->pathMatchingAlgorithm, $routes);
    }

    public function getRoutes(): RouteCollectionInterface
    {
        return $this->routes;
    }

    public function dispatch(string $requestedPath)
    {
        /**
         * @var RouteInterface $route
         */
        foreach ($this->routes as $path => $route) {
            if (null === $this->pathMatchingAlgorithm->match($requestedPath, $path)) {
                continue;
            }

            dump($route->getDispatchers()->dispatch());
        }
    }
}
