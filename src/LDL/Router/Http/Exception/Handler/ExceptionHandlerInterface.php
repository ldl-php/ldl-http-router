<?php

declare(strict_types=1);

namespace LDL\Router\Http\Exception\Handler;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Http\HttpRouterInterface;

interface ExceptionHandlerInterface
{
    public function handle(
        \Throwable $e,
        HttpRouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response
    ): void;
}
