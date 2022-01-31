<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Traits;

use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Validators\Chain\ValidatorChainInterface;

trait RouteInterfaceTrait
{
    /**
     * @var string
     */
    private $_tRouteTraitName;

    /**
     * @var string
     */
    private $_tRouteTraitDescription;

    /**
     * @var string
     */
    private $_tRouteTraitPath;

    /**
     * @var RouteDispatcherCollection
     */
    private $_tRouteTraitDispatchers;

    /**
     * @var ValidatorChainInterface
     */
    private $_tRouteTraitValidatorChain;

    public function getName(): string
    {
        return $this->_tRouteTraitName;
    }

    public function getDescription(): string
    {
        return $this->_tRouteTraitDescription;
    }

    public function getPath(): string
    {
        return $this->_tRouteTraitPath;
    }

    public function getValidatorChain(): ValidatorChainInterface
    {
        return $this->_tRouteTraitValidatorChain;
    }

    public function getDispatchers(): RouteDispatcherCollection
    {
        return $this->_tRouteTraitDispatchers;
    }
}
