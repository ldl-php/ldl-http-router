<?php

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class RouteConfigParserCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if($item instanceof RouteConfigParserInterface){
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            RouteConfigParserInterface::class
        );

        throw new TypeMismatchException($msg);
    }
}