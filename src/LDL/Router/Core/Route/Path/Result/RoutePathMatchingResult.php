<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

use LDL\Router\Core\Route\RouteInterface;

class RoutePathMatchingResult implements RoutePathMatchingResultInterface
{
    /**
     * @var RouteInterface
     */
    private $route;

    /**
     * @var RoutePathParsingResultInterface
     */
    private $result;

    /**
     * @var array|null
     */
    private $parameters;

    public function __construct(
        RoutePathParsingResultInterface $result,
        RouteInterface $route,
        ?array $parameters
    ) {
        $this->result = $result;
        $this->route = $route;
        $this->parameters = $parameters;
    }

    public function getPath(): RoutePathParsingResultInterface
    {
        return $this->result;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}
