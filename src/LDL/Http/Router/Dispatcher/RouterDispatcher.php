<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Exception\UndispatchedRouterException;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResult;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepositoryInterface;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Container\RouterContainer;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Http\Router\Container\Source\ContainerStaticSource;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\Route as PhRoute;
use Phroute\Phroute\RouteDataInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class RouterDispatcher implements RouterDispatcherInterface
{
    private $staticRouteMap;

    private $variableRouteData;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var MiddlewareChainResultInterface
     */
    private $result;

    /**
     * @var RouteInterface
     */
    private $matchedRoute;

    /**
     * @var ParameterBag|null
     */
    private $urlParameters;

    /**
     * RouterDispatcher constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->urlParameters = new ParameterBag();
    }

    public function initializeRoutes(RouteDataInterface $data) : self
    {
        $this->staticRouteMap = $data->getStaticRoutes();
        $this->variableRouteData = $data->getVariableRoutes();

        return $this;
    }

    /**
     * @return MiddlewareChainResultInterface
     * @throws UndispatchedRouterException
     */
    public function getResult() : MiddlewareChainResultInterface
    {
        if(null === $this->result){
            $msg = 'You can not obtain the result of an "undispatched" router dispatcher';
            throw new UndispatchedRouterException($msg);
        }

        return $this->result;
    }

    public function getUrlParameters() : ParameterBag
    {
        return $this->urlParameters;
    }

    /**
     * @param RouterContainerInterface $sources
     * @param string $httpMethod
     * @param string $uri
     *
     * @throws \Exception
     *
     * @return void
     *
     */
    public function dispatch(
        RouterContainerInterface $sources,
        string $httpMethod,
        string $uri
    ) : void
    {
        $dispatchers        = $sources->getDispatchers();
        $requestValidators  = $this->router->getRequestValidatorChain();
        $responseValidators = $this->router->getResponseValidatorChain();
        $requestBodyParsers = $sources->getRequestBodyParsers();

        $this->result = new MiddlewareChainResult();

        $route = null;

        /**
         * If the route is not found, an exception will be thrown
         *
         * @var Route $route
         */
        [$route, $filters, $vars] = $this->dispatchRoute($httpMethod, trim($uri, '/'));

        /**
         * Add matched parameters to url parameters source
         */
        $sources->getUrlParameters()->appendMany($vars);

        /**
         * Add current route to parameter sources
         */
        $sources->append(
            new ContainerStaticSource(
                RouterContainer::SRC_REQUEST_ROUTE_CURRENT,
                $route
            )
        );

        /**
         * Set the current route in the router
         */
        $this->router->setCurrentRoute($route);

        /**
         * Obtain request body parser if there's one
         */
        $requestBodyParser = $route->getConfig()->getRequestParser();

        /**
         * If there's a request body parser then parse the request body with the selected parser
         */
        if(null !== $requestBodyParser){
            $requestBodyParsers->select($requestBodyParser);

            $sources->append(
                new ContainerStaticSource(
                    RouterContainer::SRC_REQUEST_BODY_PARSED,
                    $requestBodyParsers->getSelectedItem()
                        ->parse($sources->get(RouterContainer::SRC_REQUEST_BODY_CONTENT))
                )
            );
        }

        /**
         * Lock request body parser repository and lock selection
         */
        $requestBodyParsers->lockSelection()
            ->lock();

        /**
         * Parse custom configuration directives before the route is about to be dispatched, these directives
         * can (mainly) modify the pre and post dispatch chains from the route and the router, selecting a different
         * response parser according to a certain custom configuration, etc.
         */
        $sources->getConfigParsers()->parse($route);

        /**
         * If the route contains exception handlers, select them
         */
        $routeExceptionHandlers = $route->getConfig()->getExceptionHandlerList();

        if($routeExceptionHandlers->count() > 0){
            $sources
                ->getExceptionHandlers()
                ->select($routeExceptionHandlers);
        }

        $sources->getExceptionHandlers()
            ->lock()
            ->lockSelection();

        /**
         * If the route contains a response parser, use the response parser configured in the route
         */
        if ($route->getConfig()->getResponseParser()) {

            $sources->getResponseParsers()
                ->select($route->getConfig()->getResponseParser())
                ->getSelectedItem()
                ->setOptions(
                    $route->getConfig()->getResponseParserOptions()
                );

        }

        /**
         * From this point on, response parsers are locked and can no longer be selected. This means that
         * any kind of middleware is not allowed to select a different response parser.
         */
        $sources->getResponseParsers()
            ->lock()
            ->lockSelection();

        /**
         * If the route contains a response formatter, use the response formatter configured in the route
         */
        if($route->getConfig()->getResponseFormatter()){
                $sources->getResponseFormatters()
                ->select($route->getConfig()->getResponseFormatter())
                ->getSelectedItem()
                ->setOptions(
                    $route->getConfig()->getResponseFormatterOptions()
                );
        }

        $sources->getResponseFormatters()
            ->lock()
            ->lockSelection();

        /**
         * If the route config has request validators, select them and validate
         */
        if($route->getConfig()->getRequestValidators()->count() > 0){
            $requestValidators->select($route->getConfig()->getRequestValidators())
                ->getSelectedItems()
                ->validate($this->router);
        }

        $requestValidators->lock()
            ->lockSelection();

        /**
         * If the route config has response validators, select them
         */
        if($route->getConfig()->getResponseValidators()->count() > 0) {
            $responseValidators->select($route->getConfig()->getResponseValidators())
                ->getSelectedItems()
                ->validate($this->router);
        }

        $responseValidators->lockSelection()
            ->lock();

        /**
         * Router pre dispatch execution
         */
        $this->dispatchMiddleware(
            'router.pre',
            $sources,
            $this->router->getPreDispatchList()->keys(),
            $dispatchers,
            $route->getConfig()->getPostDispatchers()
        );

        /**
         * Execute route pre dispatchers
         */
        $this->dispatchMiddleware(
            'route.pre',
            $sources,
            $route->getConfig()->getPreDispatchers()->keys(),
            $dispatchers,
            $route->getConfig()->getPreDispatchers()
        );

        /**
         * Execute route dispatchers
         */
        $this->dispatchMiddleware(
            'route.main',
            $sources,
            $route->getConfig()->getDispatchers()->keys(),
            $dispatchers,
            $route->getConfig()->getDispatchers(),
        );

        /**
         * Execute route post dispatchers
         */
        $this->dispatchMiddleware(
            'route.post',
            $sources,
            $route->getConfig()->getPostDispatchers()->keys(),
            $dispatchers,
            $route->getConfig()->getPostDispatchers(),
        );

        /**
         * Router post dispatch execution
         */
        $this->dispatchMiddleware(
            'router.post',
            $sources,
            $this->router->getPostDispatchList()->keys(),
            $dispatchers,
            $route->getConfig()->getPostDispatchers()
        );

        $responseValidators->validate($this->router);
    }

    private function dispatchMiddleware(
        string $context,
        RouterContainerInterface $sources,
        array $dispatcherList,
        MiddlewareChainInterface $chain,
        MiddlewareConfigRepositoryInterface $configRepository
    ) : void
    {
        if(count($dispatcherList) === 0){
            return;
        }

        $chain->filterByKeys($dispatcherList)
            ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ->lock()
            ->lockSelection()
            ->dispatch(
                $context,
                $this->router->getEventBus(),
                $sources,
                $configRepository,
                $sources->getExceptionHandlers()->getSelectedItems(),
            );
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws HttpMethodNotAllowedException
     * @throws HttpRouteNotFoundException
     */
    private function dispatchRoute($httpMethod, $uri)
    {
        if (isset($this->staticRouteMap[$uri]))
        {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        return $this->dispatchVariableRoute($httpMethod, $uri);
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws HttpMethodNotAllowedException
     */
    private function dispatchStaticRoute($httpMethod, $uri)
    {
        $routes = $this->staticRouteMap[$uri];

        if (!isset($routes[$httpMethod]))
        {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }

        return $routes[$httpMethod];
    }

    /**
     * @param $routes
     * @param $httpMethod
     * @return mixed
     * @throws HttpMethodNotAllowedException
     */
    private function checkFallbacks($routes, $httpMethod)
    {
        $additional = array(PhRoute::ANY);

        if($httpMethod === PhRoute::HEAD)
        {
            $additional[] = PhRoute::GET;
        }

        foreach($additional as $method)
        {
            if(isset($routes[$method]))
            {
                return $method;
            }
        }

        $this->matchedRoute = $routes;

        throw new HttpMethodNotAllowedException('Allow: ' . implode(', ', array_keys($routes)));
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws HttpRouteNotFoundException
     * @throws HttpMethodNotAllowedException
     */
    private function dispatchVariableRoute($httpMethod, $uri)
    {
        foreach ($this->variableRouteData as $data)
        {
            if (!preg_match($data['regex'], $uri, $matches))
            {
                continue;
            }

            $count = count($matches);

            while(!isset($data['routeMap'][$count++])){}

            $routes = $data['routeMap'][$count - 1];

            if (!isset($routes[$httpMethod]))
            {
                $httpMethod = $this->checkFallbacks($routes, $httpMethod);
            }

            foreach (array_values($routes[$httpMethod][2]) as $i => $varName)
            {
                if(!isset($matches[$i + 1]) || $matches[$i + 1] === '')
                {
                    unset($routes[$httpMethod][2][$varName]);
                }
                else
                {
                    $routes[$httpMethod][2][$varName] = $matches[$i + 1];
                }
            }

            return $routes[$httpMethod];
        }

        throw new HttpRouteNotFoundException('Route ' . $uri . ' does not exist');
    }
}
