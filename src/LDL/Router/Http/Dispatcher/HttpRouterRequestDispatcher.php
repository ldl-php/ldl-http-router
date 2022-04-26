<?php

declare(strict_types=1);

namespace LDL\Router\Http\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Http\HttpRouterInterface;
use LDL\Router\Http\Response\Exception\HttpResponseException;
use LDL\Router\Http\Route\HttpRouteInterface;

class HttpRouterRequestDispatcher implements HttpRouterDispatcherInterface
{
    public function dispatch(
        HttpRouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response
    ): void {
        $path = $router->findByRequest($request);

        if (null === $path) {
            throw new HttpResponseException('Requested route was not found', ResponseInterface::HTTP_CODE_NOT_FOUND);
        }

        $router->getValidatorChain()->validate($request);

        /**
         * @var HttpRouteInterface $route
         */
        $route = $path->getCollectedRoute()->getRoute();

        $content = $route->getResponseEncoder()->encode(
            $route->getDispatchers()
                ->dispatch(
                    $path->getCollectedRoute(),
                    ...array_values($path->getParameters())
                )
        );

        $response->setStatusCode($route->getSuccessCode());
        $response->setContent($content);
    }
}
