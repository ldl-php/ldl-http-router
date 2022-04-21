<?php

declare(strict_types=1);

namespace LDL\Router\Core;

use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Collector\RouteCollectorInterface;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollection;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollectionInterface;
use LDL\Router\Core\Route\Parsed\ParsedRoute;
use LDL\Router\Core\Route\Path\Parser\RoutePathParser;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Core\Traits\RouterInterfaceTrait;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;

class Router implements RouterInterface
{
    use RouterInterfaceTrait;

    public function __construct(
        RouteCollectionInterface $routes,
        RoutePathParserInterface $parser = null,
        RouteCollectorInterface $routeCollector = null,
        ValidatorChainInterface $chain = null
    ) {
        $this->_tRouterTraitRoutes = $routes;
        $this->_tRouterTraitParser = $parser ?? new RoutePathParser();
        $this->_tRouterTraitRouteCollector = $routeCollector ?? new RouteCollector();
        $this->_tRouterTraitValidatorChain = $chain ?? new AndValidatorChain();
    }

    public function getRouteList(): ParsedRouteCollectionInterface
    {
        $return = new ParsedRouteCollection();
        $collected = $this->_tRouterTraitRouteCollector->collect($this->_tRouterTraitRoutes);

        /**
         * @var CollectedRouteInterface $c
         */
        foreach ($collected as $c) {
            $path = $this->_tRouterTraitParser->parse(...$c->getPaths());
            /**
             * @var RouteInterface $route
             */
            $route = $c->getRoute();

            $return->append(
                new ParsedRoute(
                    $route->getName(),
                    $route->getDescription(),
                    $path->getPath(),
                    $route->getPath(),
                    $path->isDynamic(),
                    $path->getPlaceHolders(),
                    $route->getDispatchers(),
                    $route->getValidatorChain()
                )
            );
        }

        return $return;
    }
}
