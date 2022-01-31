<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Result;

use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;

interface RouteDispatcherResultInterface
{
    public function getDispatcher(): RouteDispatcherInterface;

    public function getDispatcherResult();
}
