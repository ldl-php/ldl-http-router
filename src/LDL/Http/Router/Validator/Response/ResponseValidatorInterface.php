<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Response;

use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Router\Router;

interface ResponseValidatorInterface extends NameableInterface
{
    /**
     * @param Router $router
     * @param array|null $responseResult
     * @throws Exception\ResponseValidationTerminateException
     */
    public function validate(Router $router, array $responseResult = null) : void;
}