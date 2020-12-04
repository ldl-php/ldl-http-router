<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Selection\SingleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueValidator;

class ResponseParserRepository extends ObjectCollection implements ResponseParserRepositoryInterface
{
    use SingleSelectionTrait;
    use KeyValidatorChainTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ResponseParserInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator());
    }

    public function append($item, $key = null) : CollectionInterface
    {
        parent::append($item, \mb_strtolower($item->getName()));
        return $this;
    }
}