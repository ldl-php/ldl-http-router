<?php

declare(strict_types=1);

namespace LDL\Router\Http\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Http\HttpRouterInterface;
use LDL\Router\Http\Response\Exception\HttpResponseException;

interface HttpRouterDispatcherInterface
{
    /**
     * @throws HttpResponseException
     */
    public function dispatch(
        HttpRouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response
    ): void;
}
