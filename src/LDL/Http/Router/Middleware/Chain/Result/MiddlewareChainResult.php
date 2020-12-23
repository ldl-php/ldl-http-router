<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Chain\Result;

use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class MiddlewareChainResult extends ObjectCollection implements MiddlewareChainResultInterface
{

    use ValueValidatorChainTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(
                new InterfaceComplianceItemValidator(MiddlewareChainResultItemInterface::class)
            );
    }

}
