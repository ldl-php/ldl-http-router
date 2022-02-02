<?php
/**
 * A collection which must only accept RouteInterface or RouteGroupInterface objects.
 */
declare(strict_types=1);

namespace LDL\Http\Router\Route\Collection;

use LDL\Framework\Base\Collection\Contracts\CollectionInterface;
use LDL\Http\Router\Route\Collection\Exception\DuplicateRouteException;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\TypedCollectionInterface;

interface RouteCollectionInterface extends TypedCollectionInterface
{
    /**
     * @param mixed $item
     * @param null  $key
     *
     * @throws DuplicateRouteException
     */
    public function append($item, $key = null): CollectionInterface;

    /**
     * @throws DuplicateRouteException
     */
    public function addRoute(RouteInterface $route): RouteCollectionInterface;

    /**
     * @throws DuplicateRouteException
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectionInterface;
}
