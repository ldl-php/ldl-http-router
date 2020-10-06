<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Framework\Base\Contracts\NamespaceInterface;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Selection\SingleSelectionTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;

class ResponseParserRepository extends ObjectCollection implements ResponseParserRepositoryInterface
{
    use NamespaceableTrait;
    use SingleSelectionTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ResponseParserInterface::class))
            ->append(new UniqueKeyValidator())
            ->lock();
    }

    public static function createStorageKey(NamespaceInterface $item) : string
    {
        return strtolower(sprintf('%s.%s', $item->getNamespace(), $item->getName()));
    }

    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, self::createStorageKey($item));
    }
}