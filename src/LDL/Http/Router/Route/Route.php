<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

class Route implements RouteInterface
{
    public const CONTEXT_ROUTE_EXCEPTION = 'route_exception';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteConfig
     */
    private $config;

    public function __construct(Router $router, RouteConfig $config)
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * @return RouteConfig
     */
    public function getConfig(): RouteConfig
    {
        return clone($this->config);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag $urlParameters
     * @return array
     * @throws \Exception
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : ?array
    {
        $config = $this->config;

        $result = [];

        /**
         * If any condition requires to abort the flow execution of the route, feel free
         * to throw an exception in your middleware.
         * Don't forget to add an exception handler for said exception.
         */
        $preResult = $config->getPreDispatchMiddleware()->dispatch(
            $this,
            $request,
            $response,
            $urlParameters
        );

        if(count($preResult) > 0) {
            $result['pre'] = $preResult;
        }

        $mainResult = $config->getDispatcher()->dispatch(
            $request,
            $response,
            $urlParameters
        );

        if($mainResult) {
            $result['main'] = $mainResult;
        }

        /**
         * If any condition requires to abort the flow execution of the route, feel free
         * to throw an exception in your middleware.
         * Don't forget to add an exception handler for said exception.
         */
        $postResult = $config->getPostDispatchMiddleware()->dispatch(
            $this,
            $request,
            $response,
            $urlParameters
        );

        if($postResult){
            $result['post'] = $postResult;
        }

        return $result;

    }

}