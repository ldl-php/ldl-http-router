<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collection;

use LDL\Router\Core\Route\Collection\Traits\RouteCollectionInterfaceTrait;
use LDL\Router\Core\Route\RouteInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\OrValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;
use LDL\Validators\InterfaceComplianceValidator;

class RouteCollection extends AbstractTypedCollection implements RouteCollectionInterface
{
    use RouteCollectionInterfaceTrait;

    public function __construct(
        iterable $items = null,
        string $path = '',
        ValidatorChainInterface $chain = null
    ) {
        $this->_tRouteCollectionPath = $path;

        $this->_tRouteCollectionValidatorChain = $chain ?? new AndValidatorChain();
        $this->getAppendValueValidatorChain(OrValidatorChain::class)
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RouteInterface::class))
            ->append(new InterfaceComplianceValidator(RouteCollectionInterface::class))
            ->lock();

        parent::__construct($items);
    }
}
