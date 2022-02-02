<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path\Result;

use LDL\Http\Router\Route\RouteInterface;

class RoutePathParsingResult implements RoutePathParsingResultInterface
{
    /**
     * @var bool
     */
    private $isDynamic;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array|null
     */
    private $placeHolders;

    /**
     * @var RouteInterface
     */
    private $route;

    public function __construct(
        RouteInterface $route,
        string $path,
        bool $dynamic,
        ?array $placeHolders
    ) {
        $this->route = $route;
        $this->isDynamic = $dynamic;
        $this->path = $path;
        $this->placeHolders = $placeHolders;
    }

    public function isDynamic(): bool
    {
        return $this->isDynamic;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPlaceHolders(): ?array
    {
        return $this->placeHolders;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }
}
