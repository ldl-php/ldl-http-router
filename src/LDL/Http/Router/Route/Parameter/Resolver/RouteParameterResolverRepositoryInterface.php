<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Resolver;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface RouteParameterResolverRepositoryInterface extends CollectionInterface, HasValueValidatorChainInterface
{

}
