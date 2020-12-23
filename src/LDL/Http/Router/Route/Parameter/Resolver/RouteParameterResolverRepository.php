<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Resolver;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class RouteParameterResolverRepository extends ObjectCollection implements RouteParameterResolverRepositoryInterface
{

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteParameterResolverInterface::class))
            ->lock();
    }

    /**
     * @param RouteParameterResolverInterface $item
     * @param null $key
     *
     * @throws \Exception
     *
     * @return RouteParameterResolverRepositoryInterface
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    /**
     * @param $offset
     *
     * @throws \LDL\Type\Collection\Exception\CollectionKeyException
     * @throws \LDL\Type\Collection\Exception\UndefinedOffsetException
     *
     * @return RouteParameterResolverInterface
     */
    public function offsetGet($offset)
    {
        return parent::offsetGet($offset);
    }

}