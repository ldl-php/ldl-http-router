<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher;

use LDL\Framework\Base\Contracts\DescribableInterface;
use LDL\Framework\Base\Contracts\NameableInterface;

interface RouteDispatcherInterface extends NameableInterface, DescribableInterface
{
    public function dispatch();
}
