<?php

declare(strict_types=1);

namespace LDL\Router\Core;

use LDL\Router\Core\Exception\RouteNotFoundException;
use LDL\Router\Core\Result\RouterDispatchResult;
use LDL\Router\Core\Result\RouterDispatchResultInterface;
use LDL\Router\Core\Route\Collection\RouteCollection;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Path\Parser\RoutePathParser;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;

class Router implements RouterInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var ValidatorChainInterface
     */
    private $validatorChain;

    /**
     * @var RoutePathParserInterface
     */
    private $parser;

    /**
     * @var RouteCollector
     */
    private $collector;

    public function __construct(
        RouteCollectionInterface $routes,
        RoutePathParserInterface $parser = null,
        RouteCollector $routeCollector = null,
        ValidatorChainInterface $chain = null
    ) {
        $this->routes = $routes;
        $this->parser = $parser ?? new RoutePathParser();
        $this->collector = $routeCollector ?? new RouteCollector();
        $this->validatorChain = $chain ?? new AndValidatorChain();
    }

    public function getRoutes(): RouteCollectionInterface
    {
        return $this->routes;
    }

    public function getValidatorChain(): ValidatorChainInterface
    {
        return $this->validatorChain;
    }

    public function find(string $requestedPath): RoutePathMatchingCollectionInterface
    {
        return $this->parser->match($requestedPath, $this->collector->collect($this->routes));
    }

    public function match(string $path): RouterDispatchResultInterface
    {
        $matched = $this->find($path);

        if (0 === count($matched)) {
            throw new RouteNotFoundException("Route matching path $path was not found");
        }

        /**
         * Static routes are have higher relevance since they provide us with an EXACT match
         * against the requested path.
         */
        $static = $matched->filterStatic();

        if (count($static) > 0) {
            $path = $static->get(0);

            return new RouterDispatchResult(
                $path,
                $path->getRoute()
                    ->getDispatchers()
                    ->dispatch(...array_values($path->getParameters()))
            );
        }

        $dynamic = $matched->filterDynamic();

        if (0 === count($dynamic)) {
            throw new RouteNotFoundException("Route matching path $path was not found");
        }

        $path = $dynamic->get(0);

        return new RouterDispatchResult(
            $path,
            $path->getRoute()
                ->getDispatchers()
                ->dispatch(...array_values($path->getParameters()))
        );
    }
}
