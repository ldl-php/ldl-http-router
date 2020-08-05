<?php

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Guard\RouteGuardCollection;

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

    /**
     * Obtain group guards, the idea is that said guards will be executed
     * before dispatching any route inside the route group, so instead of adding a guard for each
     * route, you can add a global guard to a route group instead.
     *
     * Examples of said guard could be the following:
     *
     * Authenticated state
     * Request content type JSON
     * etc ...
     *
     * @return RouteGuardCollection|null
     */
    public function getGuards() : ?RouteGuardCollection;

}