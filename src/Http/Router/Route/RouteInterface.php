<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Route\Cache\Config\RouteCacheConfig;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

interface RouteInterface
{
    /**
     * Brief name of the route
     * @return RouteConfig
     */
    public function getConfig() : RouteConfig;

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards() : ?RouterGuardCollection;

    /**
     * @return ParameterCollection|null
     */
    public function getParameters() : ?ParameterCollection;

    /**
     * Returns the route dispatcher which is in charge of adding logic
     * to the request.
     *
     * You could think of a dispatcher like a controller, although it only contains one single method.
     *
     * @return RouteDispatcherInterface
     */
    public function getDispatcher() : RouteDispatcherInterface;

    public function dispatch(RequestInterface $request, ResponseInterface $response) : void;
}