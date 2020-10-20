<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Guard\RouterGuardCollection;

interface RouteGroupInterface
{
    /**
     * Short route group name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Returns the route group prefix, for example: admin
     * @return string
     */
    public function getPrefix() : string;

    /**
     * Describes what do the routes under this group do, in general lines.
     *
     * @return string
     */
    public function getDescription() : string;

    /**
     * Returns the routes inside this route group
     *
     * @return RouteCollection
     */
    public function getRoutes() : RouteCollection;

}