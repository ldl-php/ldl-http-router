<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collection\Traits;

use LDL\Validators\Chain\ValidatorChainInterface;

trait RouteCollectionInterfaceTrait
{
    /**
     * @var string
     */
    private $_tRouteCollectionPath;

    /**
     * @var ValidatorChainInterface
     */
    private $_tRouteCollectionValidatorChain;

    public function getPath(): string
    {
        return $this->_tRouteCollectionPath;
    }

    public function getValidatorChain(): ValidatorChainInterface
    {
        return $this->_tRouteCollectionValidatorChain;
    }
}
