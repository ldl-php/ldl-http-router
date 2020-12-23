<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Response;

interface HasResponseValidatorChainInterface
{
    public function getResponseValidatorChain() : ResponseValidatorChain;
}