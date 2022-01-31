<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collection\Traits;

use LDL\Framework\Base\Collection\Contracts\CollectionInterface;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Group\RouteGroupInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathParsingResultInterface;
use LDL\Router\Core\Route\Path\RoutePathParserInterface;
use LDL\Router\Core\Route\RouteInterface;

trait RouteCollectionInterfaceTrait
{
    /**
     * @var RoutePathParserInterface
     */
    private $pathParser;

    public function getPathParser(): RoutePathParserInterface
    {
        return $this->pathParser;
    }

    public function append($route, $key = null): CollectionInterface
    {
        if ($route instanceof RouteGroupInterface) {
            return $this->addGroup($route);
        }

        if ($route instanceof RouteInterface) {
            return $this->addRoute($route);
        }

        return parent::append($route, $key);
    }

    public function addRoute(RouteInterface $route): RouteCollectionInterface
    {
        $result = $this->pathParser->parse($route, null, $route->getPath());
        parent::append($result, $result->getPath());

        return $this;
    }

    public function addGroup(RouteGroupInterface $group): RouteCollectionInterface
    {
        /**
         * @var RouteInterface $route
         */
        foreach ($group as $route) {
            $result = $this->pathParser->parse(
                $route,
                $group->getValidatorChain(),
                $group->getPath(),
                $route->getPath()
            );

            parent::append($result, $result->getPath());
        }

        return $this;
    }

    public function filterStaticRoutes(): RouteCollectionInterface
    {
        return $this->filter(static function (RoutePathParsingResultInterface $path) {
            return !$path->isDynamic();
        });
    }

    public function filterDynamicRoutes(): RouteCollectionInterface
    {
        return $this->filter(static function (RoutePathParsingResultInterface $path) {
            return $path->isDynamic();
        });
    }
}
