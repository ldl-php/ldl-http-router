<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class MiddlewareChainCollection extends ObjectCollection
{

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);
        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareChainInterface::class))
            ->lock();
    }

}
