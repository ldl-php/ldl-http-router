<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Validator;

interface HasValidatorChainInterface
{
    public function getValidatorChain() : RequestValidatorChain;
}