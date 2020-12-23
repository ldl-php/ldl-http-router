<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;

interface RouteInterface
{
    public const ROUTE_VERSION_PARAMETER = 'X-API-Version';
    public const ROUTE_VERSION_HEADER = 'X-API-Version';

    /**
     * @return Router
     */
    public function getRouter() : Router;

    /**
     * @return RouteConfig
     */
    public function getConfig() : RouteConfig;

}