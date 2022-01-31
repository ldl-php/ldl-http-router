<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection\Result;

use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;

interface RouteDispatcherCollectionResultInterface
{
    public function getDispatcher(): RouteDispatcherInterface;

    public function getDispatcherResult();
}
