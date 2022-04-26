<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\RouterInterface;

interface HttpRouterInterface extends RouterInterface
{
    public function findByRequest(RequestInterface $request): ?RoutePathMatchingResultInterface;

    public function dispatch(RequestInterface $request, ResponseInterface $response): void;
}
