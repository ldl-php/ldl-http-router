<?php

declare(strict_types=1);

namespace LDL\Router\Http\Collection;

use LDL\Router\Http\Validator\HttpMethodValidator;
use LDL\Type\Collection\AbstractTypedCollection;

class HttpMethodCollection extends AbstractTypedCollection
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new HttpMethodValidator())
            ->lock();

        parent::__construct($items);
    }
}
