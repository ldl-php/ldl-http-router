<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Http\Collection\HttpMethodCollection;
use LDL\Router\Http\Response\Encoder\HttpResponseEncoderInterface;

interface HttpRouteInterface extends RouteInterface
{
    public function getSuccessCode(): int;

    public function getMethods(): HttpMethodCollection;

    public function getResponseEncoder(): HttpResponseEncoderInterface;

    public function dispatch(RoutePathMatchingResultInterface $path, ResponseInterface $response): void;
}
