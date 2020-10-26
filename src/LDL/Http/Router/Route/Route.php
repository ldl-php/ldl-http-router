<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

class Route implements RouteInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteConfig
     */
    private $config;

    /**
     * @var bool
     */
    private $isDispatched = false;

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

    public function isDispatched(): bool
    {
        return $this->isDispatched;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag $urlParameters
     * @return array|null
     * @throws \Exception
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : ?array
    {
        $this->isDispatched = true;
        $config = $this->config;
        /**
         * If any condition requires to abort the flow execution of the route, feel free
         * to throw an exception in your middleware.
         * Don't forget to add an exception handler for said exception.
         */
        $mainResult = $config->getDispatchers()->dispatch(
            $this,
            $request,
            $response,
            $urlParameters
        );

        return $mainResult;
    }

}