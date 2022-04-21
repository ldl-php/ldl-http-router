<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Parsed\Collection;

use LDL\Router\Core\Route\Parsed\ParsedRouteInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class ParsedRouteCollection extends AbstractTypedCollection implements ParsedRouteCollectionInterface
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(
                new InterfaceComplianceValidator(ParsedRouteInterface::class)
            )
            ->lock();

        parent::__construct($items);
    }
}
