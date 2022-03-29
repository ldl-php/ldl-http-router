<?php

declare(strict_types=1);

namespace LDL\Router\Http\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Http\Exception\HttpRouteNotFoundException;
use LDL\Router\Http\HttpRouterInterface;

class DefaultHttpRouterRequestDispatcherInterface implements HttpRouterDispatcherInterface
{
    public function dispatch(
        HttpRouterInterface $router,
        RequestInterface $request
    ): RouteDispatcherResultCollectionInterface {
        $matched = $router->findByRequest($request);

        if (0 === count($matched)) {
            throw new HttpRouteNotFoundException('Requested route was not found', ResponseInterface::HTTP_CODE_NOT_FOUND);
        }

        $router->getValidatorChain()->validate($request);

        /**
         * Static routes have higher relevance since they provide us with an EXACT match
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
                ->dispatch($path->getCollectedRoute(), ...array_values($path->getParameters()));
        }

        $dynamic = $matched->filterDynamic();

        if (0 === count($dynamic)) {
            return null;
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