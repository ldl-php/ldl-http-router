<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Request;

interface HasValidatorChainInterface
{
    public function getRequestValidatorChain() : RequestValidatorChainInterface;
}