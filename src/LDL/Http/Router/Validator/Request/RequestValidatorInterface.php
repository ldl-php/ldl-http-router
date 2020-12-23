<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Request;

use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Router\Router;

interface RequestValidatorInterface extends NameableInterface
{
    /**
     * @param Router $router
     * @throws Exception\RequestValidationTerminateException
     * @return RequestValidatorInterface
     */
    public function validate(Router $router) : RequestValidatorInterface;
}