<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Response\Parser\Json\JsonResponseParser;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use Phroute\Phroute\RouteCollector;

class Router
{
    public const CONTEXT_ROUTER_EXCEPTION = 'router_exception';
    public const CONTEXT_ROUTER_PRE_DISPATCH = 'router_preDispatch';
    public const CONTEXT_ROUTER_POST_DISPATCH = 'router_postDispatch';

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
     * @var MiddlewareChainInterface
     */
    private $preDispatch;

    /**
     * @var Route
     */
    private $currentRoute;

    /**
     * @var MiddlewareChainInterface
     */
    private $postDispatch;

    /**
     * @var ResponseParserRepository
     */
    private $responseParserRepository;

    /**
     * @var RouterDispatcher
     */
    private $dispatcher;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ExceptionHandlerCollection $exceptionHandlerCollection = null,
        ResponseParserRepository $responseParserRepository = null,
        MiddlewareChainInterface $preDispatchMiddlewareChain = null,
        MiddlewareChainInterface $postDispatchMiddlewareChain = null
    )
    {
        $this->collector = new RouteCollector();
        $this->request = $request;
        $this->response = $response;
        $this->exceptionHandlerCollection = $exceptionHandlerCollection ?? new ExceptionHandlerCollection();
        $this->preDispatch = $preDispatchMiddlewareChain ?? new MiddlewareChain();
        $this->postDispatch = $postDispatchMiddlewareChain ?? new MiddlewareChain();

        $this->responseParserRepository = $responseParserRepository;

        if(null !== $responseParserRepository){
            return;
        }

        $jsonParser = new JsonResponseParser();
        $this->responseParserRepository = new ResponseParserRepository();
        $this->responseParserRepository->append($jsonParser);
        $this->responseParserRepository->select($jsonParser->getItemKey());
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

    public function setCurrentRoute(Route $route) : self
    {
        $this->currentRoute = $route;
        return $this;
    }

    public function getCurrentRoute() : ?Route
    {
        return $this->currentRoute;
    }

    public function getResponseParserRepository() : ResponseParserRepository
    {
        return $this->responseParserRepository;
    }

    public function getDispatcher() : RouterDispatcher
    {
        return $this->dispatcher;
    }

    public function dispatch() : ResponseInterface
    {
        try {
            $this->dispatcher = new RouterDispatcher(
                $this->collector->getData(),
                $this
            );

            $this->dispatcher->dispatch(
                $this->request->getMethod(),
                parse_url($this->request->getRequestUri(), \PHP_URL_PATH)
            );

        }catch(\Exception $e){
            /**
             * Handle global router exceptions, the exception will only be rethrown if no exception handler
             * is found.
             */
            $this->exceptionHandlerCollection
                ->handle(
                    $this,
                    $e,
                    self::CONTEXT_ROUTER_EXCEPTION
                );
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
     * @return MiddlewareChainInterface
     */
    public function getPreDispatchMiddleware() : MiddlewareChainInterface
    {
        return $this->preDispatch;
    }

    /**
     * @return MiddlewareChainInterface
     */
    public function getPostDispatchMiddleware() : MiddlewareChainInterface
    {
        return $this->postDispatch;
    }

}