<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Exception\UndispatchedRouterException;
use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Response\Exception\CustomResponseException;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteDataInterface;
use Phroute\Phroute\Route as PhRoute;
use Symfony\Component\HttpFoundation\ParameterBag;

class RouterDispatcher
{
    private $staticRouteMap;

    private $variableRouteData;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var MiddlewareChainCollection
     */
    private $result;

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
    }

    public function initializeRoutes(RouteDataInterface $data) : self
    {
        $this->staticRouteMap = $data->getStaticRoutes();
        $this->variableRouteData = $data->getVariableRoutes();

        return $this;
    }

    /**
     * @return MiddlewareChainCollection
     * @throws UndispatchedRouterException
     */
    public function getResult() : MiddlewareChainCollection
    {
        if(null === $this->result){
            $msg = 'You can not obtain the result of an "undispatched" router dispatcher';
            throw new UndispatchedRouterException($msg);
        }

        return $this->result;
    }

    public function getUrlParameters() : ?ParameterBag
    {
        return $this->urlParameters;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return void
     *
     * @throws CustomResponseException
     * @throws \Exception
     */
    public function dispatch(string $httpMethod, string $uri) : void
    {
        $this->result = new MiddlewareChainCollection();

        $route = null;

        try {
            /**
             * If the route is not found, an exception will be thrown
             *
             * @var Route $route
             */
            [$route, $filters, $vars] = $this->dispatchRoute($httpMethod, trim($uri, '/'));

            /**
             * Set the current route in the router
             */
            $this->router->setCurrentRoute($route);

            $urlParameters = new ParameterBag();
            $urlParameters->add($vars);
            $this->urlParameters = $urlParameters;

            /**
             * If the route contains a response parser, use the response parser configured in the route
             */
            if ($route->getConfig()->getResponseParser()) {
                $this->router->getResponseParserRepository()
                    ->select($route->getConfig()->getResponseParser());
            }

            /**
             * Parse custom configuration directives before the route is about to be dispatched, these directives
             * can (mainly) modify the pre and post dispatch chains from the route and the router, selecting a different
             * response parser according to a certain custom configuration, etc.
             */
            $this->router->getConfigParserRepository()->parse($route);

            $routeMiddleware = [
                'pre' => $route->getPreDispatchChain()->sortByPriority(PrioritySortingInterface::SORT_ASCENDING),
                'main' => $route->getDispatchChain()->sortByPriority(PrioritySortingInterface::SORT_ASCENDING),
                'post' => $route->getPostDispatchChain()->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ];

            /**
             * Lock all of the route's middleware
             */
            $route->lockMiddleware();

        }catch(HttpRouteNotFoundException $e) {

            $this->router->getPreDispatchChain()->append(new RouteNotFoundDispatcher());

        }catch(HttpMethodNotAllowedException $e){

            $this->router->getPreDispatchChain()->append(new RouteMethodMismatchDispatcher());

        }catch(\Exception $e) {

        }

        /**
         * From this point on, response parsers are locked and can no longer be selected. This means that
         * any kind of middleware is not allowed to select a different response parser.
         */
        $this->router->getResponseParserRepository()->lockSelection();

        $routerMiddleware = [
            'pre' => $this->router->getPreDispatchChain()->sortByPriority(PrioritySortingInterface::SORT_ASCENDING),
            'post' => $this->router->getPostDispatchChain()->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
        ];

        $this->router->lockMiddleware();

        $this->dispatchMiddleware(
            $routerMiddleware['pre'],
            $route
        );

        if(null !== $route) {
            $route->lockMiddleware();

            $this->dispatchMiddleware(
                $routeMiddleware['pre'],
                $route
            );

            $this->dispatchMiddleware(
                $routeMiddleware['main'],
                $route
            );

            $this->dispatchMiddleware(
                $routeMiddleware['post'],
                $route
            );
        }

        $this->dispatchMiddleware(
            $routerMiddleware['post'],
            $route
        );
    }

    private function dispatchMiddleware(
        MiddlewareChainInterface $chain,
        RouteInterface $route = null
    ) : void
    {
        $this->result->append($chain);

        try {
            /**
             * Dispatch chain
             */
            $chain->dispatch(
                $this->router->getRequest(),
                $this->router->getResponse(),
                $this->router,
                $this->urlParameters
            );

        }catch(CustomResponseException $e){
            throw $e;
        }catch(\Exception $e){

        }
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

            while(!isset($data['routeMap'][$count++]));

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
