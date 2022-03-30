<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route\Collection;

use LDL\Router\Core\Route\Collection\Traits\RouteCollectionInterfaceTrait;
use LDL\Router\Http\Route\HttpRouteInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\OrValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;
use LDL\Validators\InterfaceComplianceValidator;

class HttpRouteCollection extends AbstractTypedCollection implements HttpRouteCollectionInterface
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
            ->append(new InterfaceComplianceValidator(HttpRouteInterface::class))
            ->append(new InterfaceComplianceValidator(HttpRouteCollectionInterface::class))
            ->lock();

        parent::__construct($items);
    }
}
