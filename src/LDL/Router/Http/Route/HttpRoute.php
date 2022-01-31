<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route;

use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Traits\RouteInterfaceTrait;
use LDL\Router\Http\Collection\HttpMethodCollection;
use LDL\Router\Http\Route\Validator\HttpMethodValidator;
use LDL\Validators\Chain\AndValidatorChain;

class HttpRoute implements HttpRouteInterface
{
    use RouteInterfaceTrait;

    /**
     * @var HttpMethodCollection
     */
    private $methods;

    public function __construct(
        string $path,
        iterable $methods,
        iterable $dispatchers,
        string $name,
        string $description = null
    ) {
        $this->methods = new HttpMethodCollection($methods);
        $validatorChain = new AndValidatorChain();

        /*
         * The very first validator in every route is which HTTP methods does the route allows
         */
        $validatorChain->getChainItems()->append(new HttpMethodValidator($this->methods));

        $this->_tRouteTraitPath = $path;
        $this->_tRouteTraitName = $name;
        $this->_tRouteTraitDispatchers = new RouteDispatcherCollection($dispatchers);
        $this->_tRouteTraitDescription = $description ?? 'No description';
        $this->_tRouteTraitValidatorChain = $validatorChain;
    }

    public function getMethods(): HttpMethodCollection
    {
        return $this->methods;
    }
}
