<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class RouteGroup extends AbstractTypedCollection implements RouteGroupInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(
        string $path,
        iterable $items = null
    ) {
        $this->path = $path;
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RouteInterface::class))
            ->lock();

        parent::__construct($items);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
