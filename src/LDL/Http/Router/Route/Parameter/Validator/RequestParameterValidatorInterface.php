<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Validator;

use LDL\Http\Router\Route\Parameter\RouteParameterInterface;

interface RequestParameterValidatorInterface
{
    public function validate(RouteParameterInterface $parameter);
}
