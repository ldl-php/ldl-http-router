<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\Exception\InvalidContentTypeException;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

class InvalidContentTypeExceptionHandler extends AbstractExceptionHandler
{

    public function handle(
        Router $router,
        \Exception $e,
        string $context,
        ParameterBag $urlParameters=null
    ): ?int
    {
        return $e instanceof InvalidContentTypeException ? ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED : null;
    }
}