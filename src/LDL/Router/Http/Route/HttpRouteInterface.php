<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route;

use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Http\Collection\HttpMethodCollection;
use LDL\Router\Http\Response\Encoder\HttpResponseEncoderInterface;

interface HttpRouteInterface extends RouteInterface
{
    public function getSuccessCode(): int;

    public function getMethods(): HttpMethodCollection;

    public function getResponseEncoder(): HttpResponseEncoderInterface;
}
