<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Resolver;

use LDL\Framework\Base\Contracts\NameableInterface;

interface RouteParameterResolverInterface extends NameableInterface
{
    public function resolve($value);
}