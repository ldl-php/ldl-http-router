<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response\Encoder\Collection;

use LDL\Router\Http\Response\Encoder\HttpResponseEncoderInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class HttpResponseEncoderCollection extends AbstractTypedCollection implements HttpResponseEncoderCollectionInterface
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(HttpResponseEncoderInterface::class))
            ->lock();

        parent::__construct($items);
    }

    public function findByName(string $name): ?HttpResponseEncoderInterface
    {
        $name = strtolower($name);

        /**
         * @var HttpResponseEncoderInterface $encoder
         */
        foreach ($this as $encoder) {
            if (strtolower($encoder->getName()) === $name) {
                return $encoder;
            }
        }

        return null;
    }
}
