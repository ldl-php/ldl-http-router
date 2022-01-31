<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route;

use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Traits\RouteInterfaceTrait;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;

class Route implements RouteInterface
{
    use RouteInterfaceTrait;

    private $parameters;

    public function __construct(
        string $path,
        iterable $dispatchers,
        string $name,
        string $description = null,
        ValidatorChainInterface $validatorChain = null
    ) {
        $this->_tRouteTraitPath = $path;
        $this->_tRouteTraitName = $name;
        $this->_tRouteTraitDispatchers = new RouteDispatcherCollection($dispatchers);
        $this->_tRouteTraitDescription = $description ?? 'No description';
        $this->_tRouteTraitValidatorChain = $validatorChain ?? new AndValidatorChain();
    }
}
