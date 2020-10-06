<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class RouteCollection extends ObjectCollection
{
    public function __construct(iterable $items = null)
    {
        parent::__construct($items);
        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteInterface::class))
            ->lock();
    }
}