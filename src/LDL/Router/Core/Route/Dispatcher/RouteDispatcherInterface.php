<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher;

use LDL\Framework\Base\Contracts\NameableInterface;

interface RouteDispatcherInterface extends NameableInterface
{
    public function dispatch();
}
