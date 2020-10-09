<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;
use LDL\Framework\Base\Contracts\NamespaceInterface;
use LDL\Framework\Base\Contracts\PriorityInterface;

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
     * @return array|null
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArguments = []
    ) : ?array;

}