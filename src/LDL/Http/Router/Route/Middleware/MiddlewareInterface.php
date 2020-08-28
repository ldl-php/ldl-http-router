<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;

interface MiddlewareInterface
{

    /**
     * Returns the namespace of this middleware
     *
     * @return string
     */
    public function getNamespace() : string;

    /**
     * Returns the name of this middleware
     * @return string
     */
    public function getName() : string;

    /**
     * @return int
     */
    public function getPriority() : int;

    /**
     * @return bool
     */
    public function isActive() : bool;

    /**
     * @param Route $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response
    );

}