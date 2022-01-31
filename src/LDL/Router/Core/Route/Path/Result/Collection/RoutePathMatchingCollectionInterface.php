<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result\Collection;

use LDL\Type\Collection\TypedCollectionInterface;

interface RoutePathMatchingCollectionInterface extends TypedCollectionInterface
{
    public function filterStatic(): RoutePathMatchingCollectionInterface;

    public function filterDynamic(): RoutePathMatchingCollectionInterface;
}
