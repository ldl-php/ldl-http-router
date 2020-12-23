<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Response;

use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Type\Collection\Exception\ExceptionCollection;
use LDL\Type\Collection\Interfaces\Nameable\NameableCollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\MultipleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;

interface ResponseValidatorChainInterface extends HasKeyValidatorChainInterface, ResponseValidatorInterface, NameableCollectionInterface, MultipleSelectionInterface, LockableObjectInterface
{

    /**
     * @throws Exception\UndispatchedResponseValidationException
     * @return null|AbstractResponseValidator
     */
    public function getLastExecutedValidator() : ?AbstractResponseValidator;

    /**
     * @param bool $strict
     * @return ResponseValidatorChainInterface
     */
    public function getNewInstance(bool $strict = true) : ResponseValidatorChainInterface;

    /**
     * @throws Exception\UndispatchedResponseValidationException
     * @return ExceptionCollection
     */
    public function getPartialExceptions(): ExceptionCollection;

    /**
     * @return bool
     */
    public function isValidated() : bool;

}