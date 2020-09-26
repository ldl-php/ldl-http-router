<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Contracts\IsActiveInterface;
use LDL\Framework\Contracts\NamespaceInterface;
use LDL\Framework\Contracts\PriorityInterface;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;

interface MiddlewareInterface extends NamespaceInterface, IsActiveInterface, PriorityInterface
{
    /**
     * @param Route $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $urlArguments
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArguments = []
    );

}