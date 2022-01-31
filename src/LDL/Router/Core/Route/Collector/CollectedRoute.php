<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collector;

use LDL\Router\Core\Route\RouteInterface;

class CollectedRoute
{
    /**
     * @var array
     */
    private $paths;

    /**
     * @var RouteInterface
     */
    private $route;

    public function __construct(
        array $paths,
        RouteInterface $route
    ) {
        $this->paths = $paths;
        $this->route = $route;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }
}
