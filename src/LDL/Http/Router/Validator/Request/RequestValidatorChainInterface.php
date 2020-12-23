<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Request;

use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Type\Collection\Exception\ExceptionCollection;
use LDL\Type\Collection\Interfaces\Nameable\NameableCollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\MultipleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;

interface RequestValidatorChainInterface extends HasKeyValidatorChainInterface, RequestValidatorInterface, NameableCollectionInterface, MultipleSelectionInterface, LockableObjectInterface
{
    /**
     * @return AbstractRequestValidator|null
     */
    public function getLastExecutedValidator() : ?AbstractRequestValidator;

    /**
     * @param bool $strict
     * @return RequestValidatorChainInterface
     */
    public function getNewInstance(bool $strict = true) : RequestValidatorChainInterface;


    /**
     * @throws Exception\UndispatchedRequestValidationException
     * @return ExceptionCollection
     */
    public function getExceptions(): ExceptionCollection;

    /**
     * @return bool
     */
    public function isValidated() : bool;

}