<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Exception\UndispatchedRouterException;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
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

    private $router;

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
     * @return array
     * @throws UndispatchedRouterException
     */
    public function getResult() : array
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
     * @return array
     *
     * @throws \Exception
     */
    public function dispatch(string $httpMethod, string $uri) : array
    {
        $this->result = [];

        /**
         * If the route is not found, an exception will be thrown
         *
         * @var Route $route
         */
        [$route, $filters, $vars] = $this->dispatchRoute($httpMethod, trim($uri, '/'));

        $urlParameters = new ParameterBag();
        $urlParameters->add($vars);

        $this->urlParameters = $urlParameters;

        /**
         * Set the current route in the router
         */
        $this->router->setCurrentRoute($route);

        /**
         * If the route contains a response parser, use the response parser configured in the route
         */
        if($route->getConfig()->getResponseParser()){
            $this->router->getResponseParserRepository()
                ->select($route->getConfig()->getResponseParser());
        }

        /**
         * Parse custom configuration directives before the route is about to be dispatched, these directives
         * can (mainly) modify the pre and post dispatch chains from the route and the router, selecting a different
         * response parser according to a certain custom configuration, etc.
         */
        if($route->getConfig()->getCustomParsers()){
            $route->getConfig()->getCustomParsers()->parse($route);
        }

        /**
         * From this point on, response parsers are locked and can no longer be selected. This means that
         * any kind of middleware is not allowed to select a different response parser any longer from this point on.
         */
        $this->router->getResponseParserRepository()->lockSelection();

        $this->dispatchMiddleware(
            $route,
            $this->router
                ->getPreDispatchMiddleware()
                ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
                ->filterByActiveState(),
            'router',
            'pre'
        );

        $this->dispatchMiddleware(
            $route,
            $route->getConfig()
                ->getPreDispatchMiddleware()
                ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
                ->filterByActiveState(),
            'route',
            'pre'
        );

        $this->dispatchMiddleware(
            $route,
            $route->getConfig()
                ->getDispatchers()
                ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
                ->filterByActiveState(),
            'route',
            'main'
        );

        $this->dispatchMiddleware(
            $route,
            $route->getConfig()
                ->getPostDispatchMiddleware()
                ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
                ->filterByActiveState(),
            'route',
            'post'
        );

        $this->dispatchMiddleware(
            $route,
            $this->router
                ->getPostDispatchMiddleware()
                ->filterByActiveState()
                ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING),
            'router',
            'post'
        );

        return $this->result;
    }

    private function dispatchMiddleware(
        RouteInterface $route,
        MiddlewareChainInterface $chain,
        string $key,
        string $subKey
    ) : void
    {
        try {
            /**
             * Dispatch chain
             */
            $result = $chain->dispatch(
                $route,
                $this->router->getRequest(),
                $this->router->getResponse(),
                $this->urlParameters
            );

            if(null !== $result && count($result)){
                $this->result[$key][$subKey] = $result;
            }

        }catch(\Exception $e){
            $result = $chain->getResult();

            if(null !== $result && count($result)){
                $this->result[$key][$subKey] = $result;
            }

            $_key = $chain->getLastExecutedDispatcher()->getName();

            $result[$_key] = $route->getConfig()
                ->getExceptionHandlerCollection()
                ->handle(
                    $this->router,
                    $e,
                    $this->urlParameters
                );

            if(count($result)) {
                $this->result[$key][$subKey] = $result;
            }
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
