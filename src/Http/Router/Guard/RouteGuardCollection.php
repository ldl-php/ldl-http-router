<?php

namespace LDL\Http\Router\Guard;

use LDL\Type\Collection\Types\Object\ObjectCollection;

class RouteGuardCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        $msg = sprintf(
            'Expected instance of type: "%s", instance of type: "%s" was given',
            RouterGuardInterface::class,
            get_class($item)
        );

        throw new Exception\InvalidGuardObject($msg);
    }
}
