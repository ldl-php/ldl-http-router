<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Framework\Base\Contracts\DescribableInterface;
use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Router\Route\Dispatcher\Collection\RouteDispatcherCollection;

interface RouteInterface extends NameableInterface, DescribableInterface
{
    public function getPath(): string;

    public function getDispatchers(): RouteDispatcherCollection;
}
