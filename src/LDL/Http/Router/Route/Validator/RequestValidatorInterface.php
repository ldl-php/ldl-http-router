<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Validator;

use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Router\Router;

interface RequestValidatorInterface extends NameableInterface
{
    /**
     * @param bool $isStrict
     * @return AbstractRequestValidator
     */
    public function getNewInstance(bool $isStrict = true) : AbstractRequestValidator;

    /**
     * @param Router $router
     * @throws Exception\ValidationTerminateException
     */
    public function validate(Router $router) : void;
}