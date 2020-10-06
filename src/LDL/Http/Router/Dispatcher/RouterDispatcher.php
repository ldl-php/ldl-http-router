<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteDataInterface;
use Phroute\Phroute\Route as PhRoute;

class RouterDispatcher {

    private $staticRouteMap;
    private $variableRouteData;

    private $router;

    public $matchedRoute;

    /**
     * RouterDispatcher constructor.
     *
     * @param RouteDataInterface $data
     * @param Router $router
     */
    public function __construct(
        RouteDataInterface $data,
        Router $router
    )
    {
        $this->staticRouteMap = $data->getStaticRoutes();

        $this->variableRouteData = $data->getVariableRoutes();

        $this->router = $router;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     * @throws \Exception
     */
    public function dispatch(string $httpMethod, string $uri)
    {
        $result = [];

        /**
         * @var Route $route
         */
        [$route, $filters, $vars] = $this->dispatchRoute($httpMethod, trim($uri, '/'));

        if($route) {
            $this->router->setCurrentRoute($route);
        }

        $request = $this->router->getRequest();
        $response = $this->router->getResponse();

        /**
         * If the route contains a response parser, use the response parser configured in the route
         */
        if($route->getConfig()->getResponseParser()){
            $this->router->getResponseParserRepository()->select($route->getConfig()->getResponseParser());
        }

        $this->router->getResponseParserRepository()->lockSelection();

        $parser = $this->router->getResponseParserRepository()->getSelectedItem();

        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        $preDispatch = $this->router
            ->getPreDispatchMiddleware()
            ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ->filterByActiveState()
            ->dispatch(
                $route,
                $request,
                $response
            );

        if(count($preDispatch)){
            $result['router']['pre'] = $preDispatch;
        }

        if($route) {
            /**
             * Dispatch route
             */
            $result['route'] = $route->dispatch($this->router->getRequest(), $this->router->getResponse(), $vars);
        }

        $postDispatch = $this->router
            ->getPostDispatchMiddleware()
            ->filterByActiveState()
            ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ->dispatch(
                $route,
                $this->router->getRequest(),
                $this->router->getResponse()
            );

        if(count($postDispatch)){
            $result['router']['post'] = $postDispatch;
        }

        $response->setContent(
            $parser->parse(
                $result,
                Router::CONTEXT_ROUTER_POST_DISPATCH,
                $this->router
            )
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
