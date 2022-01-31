<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route;

use LDL\Framework\Base\Contracts\DescribableInterface;
use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Path\RoutePathInterface;
use LDL\Validators\Chain\ValidatorChainInterface;

interface RouteInterface extends RoutePathInterface, NameableInterface, DescribableInterface
{
    public function getDispatchers(): RouteDispatcherCollection;

    public function getValidatorChain(): ValidatorChainInterface;
}
