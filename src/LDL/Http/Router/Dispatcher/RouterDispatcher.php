<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Exception\UndispatchedRouterException;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteDataInterface;
use Phroute\Phroute\Route as PhRoute;
use Symfony\Component\HttpFoundation\ParameterBag;

class RouterDispatcher {

    private $staticRouteMap;
    private $variableRouteData;

    private $router;

    private $result;

    public $matchedRoute;

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
         * Parse custom configuration directives before the route is about to be dispatched
         */
        if($route->getConfig()->getCustomParsers()){
            $route->getConfig()->getCustomParsers()->parse($route);
        }

        $this->router->getResponseParserRepository()->lockSelection();
        $parser = $this->router->getResponseParserRepository()->getSelectedItem();

        $request = $this->router->getRequest();
        $response = $this->router->getResponse();

        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        $preDispatch = $this->router
            ->getPreDispatchMiddleware()
            ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ->filterByActiveState()
            ->dispatch(
                $route,
                $request,
                $response,
                $urlParameters
            );

        if(count($preDispatch)){
            $this->result['router']['pre'] = $preDispatch;
        }

        if($route) {
            try {
                /**
                 * Dispatch route
                 */
                $this->result['route'] = $route->dispatch(
                    $this->router->getRequest(),
                    $this->router->getResponse(),
                    $urlParameters
                );
            }catch(\Exception $e){
                /**
                 * Handle route specific exceptions.
                 *
                 * If the exception was not handled by the route exception handler,
                 * the exception handler collection will rethrow the exception so the
                 * router exception handler gets executed.
                 */
                $this->result['route'] = $route->getConfig()
                    ->getExceptionHandlerCollection()
                    ->handle(
                        $this->router,
                        $e,
                        Route::CONTEXT_ROUTE_EXCEPTION
                    );
            }
        }

        $postDispatch = $this->router
            ->getPostDispatchMiddleware()
            ->filterByActiveState()
            ->sortByPriority(PrioritySortingInterface::SORT_ASCENDING)
            ->dispatch(
                $route,
                $this->router->getRequest(),
                $this->router->getResponse(),
                $urlParameters
            );

        if(count($postDispatch)){
            $this->result['router']['post'] = $postDispatch;
        }

        return $this->result;
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
