<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Validator;

use LDL\Type\Collection\Interfaces\Nameable\NameableCollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;

interface RequestValidatorChainInterface extends HasKeyValidatorChainInterface, RequestValidatorInterface, NameableCollectionInterface
{
    public function getLastExecutedValidator() : ?AbstractRequestValidator;
}