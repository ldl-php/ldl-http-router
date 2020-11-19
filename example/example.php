<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;
use LDL\Http\Router\Middleware\AbstractMiddleware;
use LDL\Http\Router\Middleware\MiddlewareChain;

class Dispatcher extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParams = null
    )
    {
        return [
            'asdasd' => 'Chalala'
        ];
    }
}

class Dispatcher2 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParameters = null
    )
    {
        throw new \InvalidArgumentException('test');
    }
}

class TestExceptionHandler extends AbstractExceptionHandler
{
    public function handle(
        Router $router,
        \Exception $e,
        ParameterBag $urlParameters = null
    ): ?int
    {
        if(!$e instanceof InvalidArgumentException){
            return null;
        }

        return ResponseInterface::HTTP_CODE_FORBIDDEN;
    }
}

$routerExceptionHandlers = new ExceptionHandlerCollection();
$routerExceptionHandlers->append(new TestExceptionHandler('test.exception.handler'));

$response = new Response();

$chainA = new MiddlewareChain('chainA');
$chainB = new MiddlewareChain('chainB');
$chainB->append(new Dispatcher('dispatcher'));
$chainB->append(new Dispatcher2('dispatcher2'));
$chainA->append($chainB);

$router = new Router(
    Request::createFromGlobals(),
    $response,
    null,
    $routerExceptionHandlers
);

$chainA->dispatch($router->getRequest(), $router->getResponse(), $router);

var_dump($chainA->getResult());