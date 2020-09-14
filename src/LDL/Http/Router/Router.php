<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Middleware\PostDispatchMiddlewareCollection;
use LDL\Http\Router\Middleware\PreDispatchMiddlewareCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use Phroute\Phroute\HandlerResolverInterface;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

class Resolver implements HandlerResolverInterface {

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Route $route
     * @return array
     */
    public function resolve($route) : array
    {
        return [
                $route,
                'dispatch'
        ];
    }

}

class Router
{
    /**
     * @var RouteCollector
     */
    private $collector;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ?ExceptionHandlerCollection
     */
    private $exceptionHandlerCollection;

    /**
     * @var PreDispatchMiddlewareCollection
     */
    private $preDispatch;

    /**
     * @var PostDispatchMiddlewareCollection
     */
    private $postDispatch;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ExceptionHandlerCollection $exceptionHandlerCollection = null,
        PreDispatchMiddlewareCollection $preDispatchMiddlewareCollection = null,
        PostDispatchMiddlewareCollection $postDispatchMiddlewareCollection = null
    )
    {
        $this->collector = $collector ?? new RouteCollector();
        $this->request = $request;
        $this->response = $response;
        $this->exceptionHandlerCollection = $exceptionHandlerCollection ?? new ExceptionHandlerCollection();
        $this->preDispatch = $preDispatchMiddlewareCollection ?? new PreDispatchMiddlewareCollection();
        $this->postDispatch = $postDispatchMiddlewareCollection ?? new PostDispatchMiddlewareCollection();
    }

    /**
     * @param RouteInterface $route
     * @param RouteGroupInterface|null $group
     * @return Router
     *
     * @throws Exception\InvalidHttpMethodException
     */
    public function addRoute(RouteInterface $route, RouteGroupInterface $group=null) : self
    {
        $response = $this->response;

        $config = $route->getConfig();
        $method = $config->getRequestMethod();

        if(!method_exists($this->collector, $method)){
            $msg = sprintf(
                '%s is not a recognized method',
                $method
            );

            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);

            throw new Exception\InvalidHttpMethodException($msg);
        }

        $path = "v{$config->getVersion()}/{$config->getPrefix()}";

        if(null !== $group){
            $path = "{$group->getPrefix()}/$path";
        }

        $this->collector->$method($path, $route);

        return $this;
    }

    public function addGroup(RouteGroupInterface $group) : self
    {
        foreach($group->getRoutes() as $r){
            $this->addRoute($r, $group);
        }

        return $this;
    }

    public function dispatch() : ResponseInterface
    {
        try {
            $dispatcher = new RouterDispatcher(
                $this->collector->getData(),
                $this
            );

            $dispatcher->dispatch(
                $this->request->getMethod(),
                parse_url($this->request->getRequestUri(), \PHP_URL_PATH)
            );

        }catch(\Exception $e){

            $this->exceptionHandlerCollection->handle($this, $e);

        }

        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return RouteCollector
     */
    public function getRouteCollector(): RouteCollector
    {
        return $this->collector;
    }

    /**
     * @return ExceptionHandlerCollection
     */
    public function getExceptionHandlerCollection(): ExceptionHandlerCollection
    {
        return $this->exceptionHandlerCollection;
    }

    /**
     * @return PreDispatchMiddlewareCollection|null
     */
    public function getPreDispatchMiddleware() : ?PreDispatchMiddlewareCollection
    {
        return $this->preDispatch;
    }

    /**
     * @return PostDispatchMiddlewareCollection|null
     */
    public function getPostDispatchMiddleware() : ?PostDispatchMiddlewareCollection
    {
        return $this->postDispatch;
    }

}