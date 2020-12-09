<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Handler\HttpMethodNotAllowedExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\HttpRouteNotFoundExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\InvalidContentTypeExceptionHandler;
use LDL\Http\Router\Route\Validator\AbstractRequestValidator;
use LDL\Http\Router\Route\Validator\Exception\ValidateException;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Middleware\AbstractMiddleware;
use LDL\Http\Router\Middleware\DispatcherRepository;
use LDL\Http\Router\Route\Validator\RequestValidatorChain;

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
            'result' => $urlParams->get('urlName')
        ];
    }
}

class PostDispatch extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParams = null
    )
    {
        /**
         * @var \LDL\Http\Router\Response\Formatter\ResponseFormatterInterface $formatter
         */
        $formatter = $router->getResponseFormatterRepository()->getSelectedItem();

        $formatter->format($router->getDispatcher()->getResult(), false);
        return ['duplicate result' => $formatter->getResult()];
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

class Dispatcher3 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParams = null
    )
    {
        return [
            'result' => $urlParams->get('urlName')
        ];
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

class CustomDispatch1 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $parameterBag=null
    )
    {
        return ['result' => 'pre dispatch result!'];
    }
}

class CustomDispatch2 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $parameterBag=null
    )
    {
        return ['result' => 'post dispatch result!'];
    }
}

/**
 * Class ConfigParser
 *
 * Useful for plugin developers to implement a custom route configuration
 */
class ConfigParser implements RouteConfigParserInterface
{
    public function parse(RouteInterface $route): void
    {
        $rawConfig = $route->getConfig()->getRawConfig();

        if(!array_key_exists('customConfig', $rawConfig)){
            return;
        }

    }
}

$routerExceptionHandlers = new ExceptionHandlerCollection();

$routerExceptionHandlers->append(new HttpMethodNotAllowedExceptionHandler('http.method.not.allowed'))
->append(new HttpRouteNotFoundExceptionHandler('http.route.not.found'))
->append(new InvalidContentTypeExceptionHandler('http.invalid.content'));

$configParserRepository = new RouteConfigParserRepository();
$configParserRepository->append(new ConfigParser());

$response = new Response();

class RouterPreDispatch extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router, ParameterBag $urlParameters = null
    )
    {
        return ['age' => $request->get('age')];
    }
}

class RouterPostDispatch extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router, ParameterBag $urlParameters = null
    )
    {
        return ['post global'];
    }
}

class AgeValidator extends AbstractRequestValidator
{
    private const NAME = 'age.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getRequest();

        if((int) $request->get('age') < 50){
            throw new ValidateException("Age must be greater than 50", Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

class NameLengthValidator extends AbstractRequestValidator
{
    private const NAME = 'name.length.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getRequest();

        if(strlen($request->get('name')) < 3){
            throw new ValidateException("Name must be larger than 3", Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

class ContentTypeValidator extends AbstractRequestValidator
{
    private const NAME = 'content-type.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getRequest();

        if($request->getHeaderBag()->get('Content-Type') !== 'application/json'){
            throw new ValidateException("Invalid content type", Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

$chainPre = new \LDL\Http\Router\Middleware\MiddlewareChain('globalPre');
$chainPre->append(new RouterPreDispatch('RouterPre'));

$chainPost = new \LDL\Http\Router\Middleware\MiddlewareChain('globalPost');
$chainPost->append(new RouterPostDispatch('RouterPost'));

$router = new Router(
    Request::createFromGlobals(),
    $response,
    $configParserRepository,
    $routerExceptionHandlers,
    new ResponseParserRepository(),
    null,
    $chainPre,
    $chainPost
);

//$router->getValidatorChain()->append(new ContentTypeValidator());

$requestValidatorsRepository = new RequestValidatorChain();
$requestValidatorsRepository->append(new AgeValidator())
->append(new ContentTypeValidator())
->append(new NameLengthValidator());

$dispatcherRepository = new DispatcherRepository();
$dispatcherRepository->append(new Dispatcher('dispatcher'))
    ->append(new Dispatcher2('dispatcher2'))
    ->append(new Dispatcher3('dispatcher3'))
    ->append(new PostDispatch('post.dispatch'));

$routeExceptionHandlers = new ExceptionHandlerCollection();
$routeExceptionHandlers->append(new TestExceptionHandler('test.exception.handler'));

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router,
    $requestValidatorsRepository,
    $dispatcherRepository,
    $routeExceptionHandlers
);

$group = new RouteGroup('Test Group', 'test', $routes);

$router->addGroup($group);

$router->dispatch()->send();
