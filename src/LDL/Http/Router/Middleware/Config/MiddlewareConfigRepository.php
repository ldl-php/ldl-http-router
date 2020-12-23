<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class MiddlewareConfigRepository extends ObjectCollection implements MiddlewareConfigRepositoryInterface
{
    public function __construct(iterable $items = null)
    {
        parent::__construct($items);
        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(
                MiddlewareConfigInterface::class
            ))
            ->lock();
    }

    /**
     * @param MiddlewareConfigInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function get(string $name): MiddlewareConfigInterface
    {
        return $this->offsetGet($name);
    }
}