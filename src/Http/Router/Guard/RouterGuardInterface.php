<?php

namespace LDL\Http\Router\Guard;

use LDL\HTTP\Core\Request\RequestInterface;
use LDL\HTTP\Core\Request\ResponseInterface;

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
     */
    public function validate(
        RequestInterface $request,
        ResponseInterface $response
    ) : void;
}