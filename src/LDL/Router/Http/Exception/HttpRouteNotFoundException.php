<?php

declare(strict_types=1);

namespace LDL\Router\Http\Exception;

use LDL\Http\Core\Response\ResponseInterface;
use Throwable;

class HttpRouteNotFoundException extends HttpRouterException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, ResponseInterface::HTTP_CODE_NOT_FOUND, $previous);
    }
}
