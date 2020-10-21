<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
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

    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : ?array;
}