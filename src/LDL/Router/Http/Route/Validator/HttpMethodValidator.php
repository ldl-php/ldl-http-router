<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route\Validator;

use LDL\Framework\Base\Constants;
use LDL\Framework\Base\Exception\InvalidArgumentException;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Http\Collection\HttpMethodCollection;
use LDL\Validators\NegatedValidatorInterface;
use LDL\Validators\Traits\NegatedValidatorTrait;
use LDL\Validators\Traits\ValidatorValidateTrait;
use LDL\Validators\ValidatorInterface;

class HttpMethodValidator implements ValidatorInterface, NegatedValidatorInterface
{
    use NegatedValidatorTrait;
    use ValidatorValidateTrait;

    /**
     * @var HttpMethodCollection
     */
    private $methods;

    public function __construct(HttpMethodCollection $methods, bool $negated = false)
    {
        $this->methods = $methods;
        $this->_tNegated = $negated;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Validate that the request method is%s: %s',
            $this->_tNegated ? ' NOT' : '',
            $this->methods->implode(' or ')
        );
    }

    /**
     * @param RequestInterface $request
     *
     * @throws InvalidArgumentException
     */
    public function assertTrue($request): void
    {
        $method = $request->getMethod();

        if ($this->methods->hasValue($method, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR)) {
            return;
        }

        throw new InvalidArgumentException("Route does not match requested method $method");
    }

    /**
     * @param RequestInterface $request
     *
     * @throws InvalidArgumentException
     */
    public function assertFalse($request): void
    {
        $method = $request->getMethod();

        if (!$this->methods->hasValue($method, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR)) {
            return;
        }

        throw new InvalidArgumentException("Route matches requested method $method");
    }
}
