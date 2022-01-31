<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collector;

use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\RouteInterface;

class RouteCollector implements RouteCollectorInterface
{
    public function collect(RouteCollectionInterface $collection): iterable
    {
        if (0 === count($collection)) {
            return [];
        }

        /**
         * @var RouteInterface|RouteCollectionInterface $route
         */
        foreach ($collection as $route) {
            if (!$route instanceof RouteCollectionInterface) {
                yield new CollectedRoute([$collection->getPath(), $route->getPath()], $route);
                continue;
            }

            /**
             * @var CollectedRoute $c
             */
            foreach ($this->collect($route) as $c) {
                /*
                 * Prepend all available route collection validations to the route
                 */
                $c->getRoute()
                    ->getValidatorChain()
                    ->getChainItems()
                    ->unshiftMany($route->getValidatorChain()->getChainItems());

                yield new CollectedRoute(
                    array_merge([$collection->getPath()], $c->getPaths()),
                    $c->getRoute()
                );
            }
        }
    }
}
