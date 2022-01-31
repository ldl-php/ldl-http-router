<?php

declare(strict_types=1);

namespace LDL\Router\Http\Validator;

use LDL\Framework\Base\Contracts\Type\ToStringInterface;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Validators\Exception\TypeMismatchException;
use LDL\Validators\NegatedValidatorInterface;
use LDL\Validators\Traits\NegatedValidatorTrait;
use LDL\Validators\Traits\ValidatorDescriptionTrait;
use LDL\Validators\Traits\ValidatorValidateTrait;
use LDL\Validators\ValidatorInterface;

class HttpMethodValidator implements ValidatorInterface, NegatedValidatorInterface
{
    use ValidatorValidateTrait;
    use NegatedValidatorTrait;
    use ValidatorDescriptionTrait;
    private const METHODS = [
        RequestInterface::HTTP_METHOD_GET,
        RequestInterface::HTTP_METHOD_POST,
        RequestInterface::HTTP_METHOD_PATCH,
        RequestInterface::HTTP_METHOD_CONNECT,
        RequestInterface::HTTP_METHOD_HEAD,
        RequestInterface::HTTP_METHOD_OPTIONS,
        RequestInterface::HTTP_METHOD_PURGE,
        RequestInterface::HTTP_METHOD_PUT,
        RequestInterface::HTTP_METHOD_TRACE,
        RequestInterface::HTTP_METHOD_DELETE,
    ];

    private const DESCRIPTION = 'Validate that a string is a valid HTTP method';

    public function __construct(
        bool $negated = false,
        string $description = null
    ) {
        $this->_tNegated = $negated;
        $this->_tDescription = $description ?? self::DESCRIPTION;
    }

    public function assertTrue($value): void
    {
        if ($value instanceof ToStringInterface) {
            $value = $value->toString();
        }

        if (!is_string($value)) {
            $msg = sprintf(
                'Value expected for "%s", must be of type string or an instanceof "%s", "%s" was given',
                __CLASS__,
                ToStringInterface::class,
                gettype($value)
            );

            throw new TypeMismatchException($msg);
        }

        if (in_array(strtoupper($value), self::METHODS, true)) {
            return;
        }

        $msg = sprintf(
            'Value expected for "%s", must be a valid HTTP method (%s), "%s" was given',
            implode(' or ', self::METHODS),
            __CLASS__,
            gettype($value)
        );

        throw new TypeMismatchException($msg);
    }

    public function assertFalse($value): void
    {
        if ($value instanceof ToStringInterface) {
            $value = $value->toString();
        }

        if (!is_string($value)) {
            $msg = sprintf(
                'Value expected for "%s", must be of type string or an instanceof "%s", "%s" was given',
                __CLASS__,
                ToStringInterface::class,
                gettype($value)
            );

            throw new TypeMismatchException($msg);
        }

        if (!in_array(strtoupper($value), self::METHODS, true)) {
            return;
        }

        $msg = sprintf(
            'Value expected for "%s", must be a valid HTTP method (%s), "%s" was given',
            implode(' or ', self::METHODS),
            __CLASS__,
            gettype($value)
        );

        throw new TypeMismatchException($msg);
    }
}
