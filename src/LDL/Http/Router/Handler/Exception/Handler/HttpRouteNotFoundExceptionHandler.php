<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

class HttpRouteNotFoundExceptionHandler extends AbstractExceptionHandler implements ModifiesResponseInterface
{
    public function getContent(): ?array
    {
        return null;
    }

    public function handle(
        Router $router,
        \Exception $e,
        ParameterBag $urlParameters=null
    ) : ?int
    {
        return $e instanceof HttpRouteNotFoundException ? ResponseInterface::HTTP_CODE_NOT_FOUND : null;
    }
}