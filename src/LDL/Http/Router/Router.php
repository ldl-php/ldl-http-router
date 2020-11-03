<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Response\Formatter\ResponseFormatter;
use LDL\Http\Router\Response\Formatter\ResponseFormatterInterface;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepository;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepositoryInterface;
use LDL\Http\Router\Response\Parser\Json\JsonResponseParser;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepository;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepositoryInterface;
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
     * @var Route
     */
    private $currentRoute;

    /**
     * @var ResponseParserRepository
     */
    private $responseParserRepository;

    /**
     * @var ResponseFormatterRepository
     */
    private $responseFormatterRepository;

    /**
     * @var MiddlewareChainInterface
     */
    private $preDispatchChain;

    /**
     * @var MiddlewareChainInterface
     */
    private $postDispatchChain;

    /**
     * @var RouteConfigParserRepositoryInterface
     */
    private $configParserRepository;

    /**
     * @var RouterDispatcher
     */
    private $dispatcher;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        RouteConfigParserRepositoryInterface $configParserCollection = null,
        ExceptionHandlerCollection $exceptionHandlerCollection = null,
        ResponseParserRepository $responseParserRepository = null,
        ResponseFormatterRepositoryInterface $responseFormatterRepository = null,
        MiddlewareChainInterface $preDispatcherRepository = null,
        MiddlewareChainInterface $postDispatcherRepository = null
    )
    {
        $this->collector = new RouteCollector();
        $this->request = $request;
        $this->response = $response;
        $this->exceptionHandlerCollection = $exceptionHandlerCollection ?? new ExceptionHandlerCollection();
        $this->preDispatchChain = $preDispatcherRepository ?? new MiddlewareChain('pre');
        $this->postDispatchChain = $postDispatcherRepository ?? new MiddlewareChain('post');
        $this->configParserRepository = $configParserCollection ?? new RouteConfigParserRepository();

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
         * and select it, this can of course be changed by the response parser set in the route configuration.
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

        $defaultResponseFormatter = new ResponseFormatter('ldl.response.formatter.default');

        /**
         * If no response formatter repo is passed, create a new instance
         */
        if(null === $responseFormatterRepository){
            $responseFormatterRepository = new ResponseFormatterRepository();
        }

        /**
         * We always need a response formatter to format a response, so we add the default response formatter
         * and select it, this can of course be changed by the response formatter configuration directive
         * in the each route's configuration.
         *
         * But for all other responses which do not have a response formatter configuration directive, the
         * default response parser will be used.
         */
        if(
            null === $responseFormatterRepository->getSelectedKey() &&
            false === $responseFormatterRepository->hasKey($defaultResponseFormatter->getName())
        ){
            $responseFormatterRepository->append($defaultResponseFormatter);
            $responseFormatterRepository->select($defaultResponseFormatter->getName());
        }

        $this->responseFormatterRepository = $responseFormatterRepository;

    }

    public function getConfigParserRepository() : RouteConfigParserRepositoryInterface
    {
        return $this->configParserRepository;
    }

    /**
     * @return MiddlewareChainInterface
     */
    public function getPreDispatchChain() : MiddlewareChainInterface
    {
        return $this->preDispatchChain;
    }

    /**
     * @return MiddlewareChainInterface
     */
    public function getPostDispatchChain() : MiddlewareChainInterface
    {
        return $this->postDispatchChain;
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

    public function lockMiddleware() : Router
    {
        $this->preDispatchChain->lock();
        $this->postDispatchChain->lock();

        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function dispatch() : ResponseInterface
    {
        $this->dispatcher->initializeRoutes($this->collector->getData());

        $this->dispatcher
            ->dispatch(
                $this->request->getMethod(),
                parse_url($this->request->getRequestUri(), \PHP_URL_PATH)
            );

        /**
         * @var ResponseFormatterInterface $formatter
         */
        $formatter = $this->responseFormatterRepository->getSelectedItem();

        $result = $formatter->format($this, $this->dispatcher->getResult());

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