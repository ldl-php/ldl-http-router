<?php

namespace LDL\HTTP\Router\Route\Dispatcher;

use LDL\HTTP\Core\Request\RequestInterface;
use LDL\HTTP\Core\Request\ResponseInterface;

interface RouteDispatcherInterface
{

    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface;
}
