<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Chain\Result;

use LDL\Http\Router\Middleware\MiddlewareInterface;

interface MiddlewareChainResultItemInterface
{
    public function getDispatcher() : MiddlewareInterface;

    public function getResult();

    public function getContext() : string;
}
