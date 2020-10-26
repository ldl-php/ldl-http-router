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
     * @var MiddlewareChainInterface
     */
    private $dispatcherChain;

    /**
     * @var RouterDispatcher
     */
    private $dispatcher;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ExceptionHandlerCollection $exceptionHandlerCollection = null,
        ResponseParserRepository $responseParserRepository = null,
        MiddlewareChainInterface $routeDispatcherRepository = null,
        MiddlewareChainInterface $preDispatchMiddlewareChain = null,
        MiddlewareChainInterface $postDispatchMiddlewareChain = null
    )
    {
        $this->collector = new RouteCollector();
        $this->request = $request;
        $this->response = $response;
        $this->exceptionHandlerCollection = $exceptionHandlerCollection ?? new ExceptionHandlerCollection();
        $this->preDispatch = $preDispatchMiddlewareChain ?? new MiddlewareChain();
        $this->dispatcherChain = $routeDispatcherRepository ?? new MiddlewareChain();
        $this->postDispatch = $postDispatchMiddlewareChain ?? new MiddlewareChain();

        $this->dispatcher = new RouterDispatcher($this);

        $jsonParser = new JsonResponseParser();

        /**
         * If no response parser repo is passed, create a new instance
         */
        if(null === $responseParserRepository){
            $responseParserRepository = new ResponseParserRepository();
        }

        /**
         * We always need a response parser to reply to a request, so we add the JSON parser
         * and select it, this can of course be changed by the response parser set in the route.
         *
         * But for all other requests which do not have a response parser configuration directive, the JSON parser
         * will be used.
         */
        if(
            null === $responseParserRepository->getSelectedKey() &&
            false === $responseParserRepository->hasKey($jsonParser->getItemKey())
        ){
            $responseParserRepository->append($jsonParser);
            $responseParserRepository->select($jsonParser->getItemKey());
        }

        $this->responseParserRepository = $responseParserRepository;
    }

    /**
     * @return MiddlewareChainInterface
     */
    public function getDispatcherChain() : MiddlewareChainInterface
    {
        return $this->dispatcherChain;
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

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function dispatch() : ResponseInterface
    {
        $result = null;

        try {
            $this->dispatcher->initializeRoutes($this->collector->getData());

            $result = $this->dispatcher
                ->dispatch(
                    $this->request->getMethod(),
                    parse_url($this->request->getRequestUri(), \PHP_URL_PATH)
            );

        }catch(\Exception $e){
            /**
             * Handle global router exceptions, the exception will only be rethrown if no exception handler
             * is found.
             *
             * Needed for 404 exceptions or wrong method exceptions
             */
            $result = $this->exceptionHandlerCollection
                ->handle(
                    $this,
                    $e,
                    $this->dispatcher->getUrlParameters()
                );
        }

        if(null === $result){
            return $this->response;
        }

        /**
         * @var ResponseParserInterface $parser
         */
        $parser = $this->responseParserRepository->getSelectedItem();

        /**
         * Set the content type header according to the response parser
         */
        $this->response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        $this->response->setContent(
            $parser->parse(
                $result,
                $this
            )
        );

        return $this->response;
    }

}