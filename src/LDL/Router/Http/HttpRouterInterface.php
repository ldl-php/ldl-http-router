<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\RouterInterface;

interface HttpRouterInterface extends RouterInterface
{
    public function findByRequest(RequestInterface $request): RoutePathMatchingCollectionInterface;

    public function dispatch(RequestInterface $request);
}
