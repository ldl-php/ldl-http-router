<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Symfony\Component\HttpFoundation\ParameterBag;

class HttpMethodNotAllowedExceptionHandler extends AbstractExceptionHandler
{
    public function handle(
        Router $router,
        \Exception $e,
        ParameterBag $urlParameters=null
    ): ?int
    {
        return $e instanceof HttpMethodNotAllowedException ? ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED : null;
    }
}