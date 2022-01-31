<?php
/**
 * A collection which must only accept RouteInterface or RouteGroupInterface objects.
 */
declare(strict_types=1);

namespace LDL\Router\Core\Route\Collection;

use LDL\Router\Core\Route\Path\RoutePathInterface;
use LDL\Type\Collection\TypedCollectionInterface;
use LDL\Validators\Chain\ValidatorChainInterface;

interface RouteCollectionInterface extends TypedCollectionInterface, RoutePathInterface
{
    public function getValidatorChain(): ValidatorChainInterface;
}
