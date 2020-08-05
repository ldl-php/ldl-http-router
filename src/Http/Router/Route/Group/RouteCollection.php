<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;

class RouteCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if($item instanceof RouteInterface){
            return;
        }

        $msg = sprintf(
            'Expected instance of type: "%s", instance of type: "%s" was given',
            RouteInterface::class,
            get_class($item)
        );

        throw new Exception\InvalidRouteObject($msg);
    }
}