<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

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

    public function __construct(
        string $path,
        bool $dynamic,
        ?array $placeHolders
    ) {
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
}
