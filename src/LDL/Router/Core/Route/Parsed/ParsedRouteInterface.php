<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Parsed;

use LDL\Framework\Base\Contracts\DescribableInterface;
use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Framework\Base\Contracts\Type\ToArrayInterface;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollectionInterface;
use LDL\Type\Collection\Interfaces\Type\ToPrimitiveArrayInterface;
use LDL\Validators\Chain\ValidatorChainInterface;

interface ParsedRouteInterface extends NameableInterface, DescribableInterface, ToArrayInterface, ToPrimitiveArrayInterface
{
    public function getParsedPath(): string;

    public function getOriginalPath(): string;

    public function isDynamic(): bool;

    public function getPlaceholders(): array;

    public function getDispatchers(): RouteDispatcherCollectionInterface;

    public function getValidatorChain(): ValidatorChainInterface;
}
