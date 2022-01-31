<?php

declare(strict_types=1);

namespace LDL\Router\Http\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Http\HttpRouterInterface;

interface HttpRouterDispatcher
{
    /**
     * @return mixed
     */
    public function dispatch(
        HttpRouterInterface $router,
        RequestInterface $request
    );
}
