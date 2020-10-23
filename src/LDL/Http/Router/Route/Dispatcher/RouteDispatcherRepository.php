<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Dispatcher;

use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;

class RouteDispatcherRepository extends ObjectCollection implements HasKeyValidatorChainInterface
{
    use KeyValidatorChainTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteDispatcherInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueKeyValidator())
            ->lock();
    }

    /**
     * @param RouteDispatcherInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, strtolower($item->getName()));
    }
}