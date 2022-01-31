<?php

declare(strict_types=1);

namespace LDL\Router\Core\Traits;

use LDL\Router\Core\Exception\RouteNotFoundException;
use LDL\Router\Core\Route\Collection\RouteCollection;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\RouteCollectorInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Validators\Chain\ValidatorChainInterface;

trait RouterInterfaceTrait
{
    /**
     * @var RouteCollection
     */
    private $_tRouterTraitRoutes;

    /**
     * @var ValidatorChainInterface
     */
    private $_tRouterTraitValidatorChain;

    /**
     * @var RoutePathParserInterface
     */
    private $_tRouterTraitParser;

    /**
     * @var RouteCollectorInterface
     */
    private $_tRouterTraitRouteCollector;

    public function getRoutes(): RouteCollectionInterface
    {
        return $this->_tRouterTraitRoutes;
    }

    public function getValidatorChain(): ValidatorChainInterface
    {
        return $this->_tRouterTraitValidatorChain;
    }

    public function find(string $requestedPath): RoutePathMatchingCollectionInterface
    {
        return $this->_tRouterTraitParser
            ->match(
                $requestedPath,
                $this->_tRouterTraitRouteCollector->collect($this->_tRouterTraitRoutes)
            );
    }

    public function match(string $path): RouteDispatcherResultCollectionInterface
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
            /**
             * @var RoutePathMatchingResultInterface $path
             */
            $path = $static->get(0);

            return $path->getCollectedRoute()
                ->getRoute()
                ->getDispatchers()
                ->dispatch($path->getCollectedRoute(), ...array_values($path->getParameters())
                );
        }

        $dynamic = $matched->filterDynamic();

        if (0 === count($dynamic)) {
            throw new RouteNotFoundException("Route matching path $path was not found");
        }

        /**
         * @var RoutePathMatchingResultInterface $path
         */
        $path = $dynamic->get(0);

        return $path->getCollectedRoute()
            ->getRoute()
            ->getDispatchers()
            ->dispatch($path->getCollectedRoute(), ...array_values($path->getParameters()));
    }
}
