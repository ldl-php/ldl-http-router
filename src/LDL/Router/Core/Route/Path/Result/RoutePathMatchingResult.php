<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;

class RoutePathMatchingResult implements RoutePathMatchingResultInterface
{
    /**
     * @var RoutePathParsingResultInterface
     */
    private $result;

    /**
     * @var array|null
     */
    private $parameters;

    /**
     * @var CollectedRouteInterface
     */
    private $collectedRoute;

    public function __construct(
        CollectedRouteInterface $collectedRoute,
        RoutePathParsingResultInterface $result,
        ?array $parameters
    ) {
        $this->collectedRoute = $collectedRoute;
        $this->result = $result;
        $this->parameters = $parameters;
    }

    public function getPath(): RoutePathParsingResultInterface
    {
        return $this->result;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function getCollectedRoute(): CollectedRouteInterface
    {
        return $this->collectedRoute;
    }
}
