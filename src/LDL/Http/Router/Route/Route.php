<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;

class Route implements RouteInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteConfig
     */
    private $config;

    public function __construct(
        Router $router,
        RouteConfig $config
    )
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * @return RouteConfig
     */
    public function getConfig(): RouteConfig
    {
        return clone($this->config);
    }

}