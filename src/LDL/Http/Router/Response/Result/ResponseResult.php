<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Result;

use LDL\Framework\Base\Traits\LockableObjectInterfaceTrait;
use LDL\Type\Collection\AbstractCollection;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class ResponseResult extends AbstractCollection implements ResponseResultInterface
{
    use ValueValidatorChainTrait;
    use LockableObjectInterfaceTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(
                new InterfaceComplianceItemValidator(Item\ResponseResultItemInterface::class)
            )
            ->lock();
    }

}