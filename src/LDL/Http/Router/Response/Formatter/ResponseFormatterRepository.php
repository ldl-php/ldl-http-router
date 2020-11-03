<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Traits\Selection\SingleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;

class ResponseFormatterRepository extends ObjectCollection implements ResponseFormatterRepositoryInterface
{
    use KeyValidatorChainTrait;
    use SingleSelectionTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(
                new InterfaceComplianceItemValidator(ResponseFormatterInterface::class)
            );

        $this->getKeyValidatorChain()
            ->append(
                new UniqueKeyValidator()
            );
    }

    /**
     * @param ResponseFormatterInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $item->getName());
    }
}