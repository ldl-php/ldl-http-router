<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Router\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Http\Router\Route\Path\RoutePathParserInterface;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var RoutePathParserInterface
     */
    private $path;

    /**
     * @var RouteDispatcherCollection
     */
    private $dispatchers;

    public function __construct(
        string $path,
        iterable $dispatchers,
        string $name,
        string $description = null
    ) {
        $this->path = $path;
        $this->dispatchers = new RouteDispatcherCollection($dispatchers);
        $this->name = $name;
        $this->description = $description ?? 'No description';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDispatchers(): RouteDispatcherCollection
    {
        return $this->dispatchers;
    }
}
