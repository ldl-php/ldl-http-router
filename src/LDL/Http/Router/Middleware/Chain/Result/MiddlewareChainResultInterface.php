<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Chain\Result;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface MiddlewareChainResultInterface extends CollectionInterface, HasValueValidatorChainInterface
{

}
