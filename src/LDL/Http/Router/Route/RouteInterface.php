<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RouteInterface
{
    public const ROUTE_VERSION_PARAMETER = 'X-API-Version';
    public const ROUTE_VERSION_HEADER = 'X-API-Version';

    /**
     * Brief name of the route
     * @return RouteConfig
     */
    public function getConfig() : RouteConfig;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag|null $urlParameters
     * @throws \Exception
     * @return array|null
     *
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : ?array;
    /**
     * @return Router
     */
    public function getRouter() : Router;

    /**
     * @return bool
     */
    public function isDispatched() : bool;
}