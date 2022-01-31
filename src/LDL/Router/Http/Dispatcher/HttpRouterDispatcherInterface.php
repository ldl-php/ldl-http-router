<?php

declare(strict_types=1);

namespace LDL\Router\Http\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Http\HttpRouterInterface;

interface HttpRouterDispatcherInterface
{
    /**
     * @return mixed
     */
    public function dispatch(
        HttpRouterInterface $router,
        RequestInterface $request
    ): RouteDispatcherResultCollectionInterface;
}
