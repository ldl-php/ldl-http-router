<?php declare(strict_types=1);

namespace LDL\Http\Router\Repository;

use LDL\Type\Collection\AbstractCollection;
use LDL\Type\Collection\Interfaces\Validation\HasValidatorChainInterface;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Validator\File\ReadableFileValidator;

class JsonRepository extends AbstractCollection implements JsonRepositoryInterface, HasValidatorChainInterface
{
    use ValueValidatorChainTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);
        $this->getValidatorChain()
            ->append(new ReadableFileValidator())
            ->lock();
    }

}