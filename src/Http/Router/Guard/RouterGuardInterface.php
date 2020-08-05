<?php declare(strict_types=1);

namespace LDL\Http\Router\Guard;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;

interface RouterGuardInterface
{
    public const VALIDATE_BEFORE = 'before';
    public const VALIDATE_AFTER = 'after';

    /**
     * Returns which type of validation should take place, before or after
     * @return string
     */
    public function getType() : string;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterCollection $parameters
     */
    public function validate(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterCollection $parameters = null
    ) : void;
}