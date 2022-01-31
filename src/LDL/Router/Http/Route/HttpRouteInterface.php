<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route;

use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Http\Collection\HttpMethodCollection;

interface HttpRouteInterface extends RouteInterface
{
    public function getMethods(): HttpMethodCollection;
}
