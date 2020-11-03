<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollectionInterface;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Router;

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
     * @var MiddlewareChainInterface
     */
    private $preDispatchers;

    /**
     * @var MiddlewareChainInterface
     */
    private $dispatcherChain;

    /**
     * @var MiddlewareChainInterface
     */
    private $postDispatchers;

    /**
     * @var ExceptionHandlerCollectionInterface
     */
    private $exceptionHandlers;

    public function __construct(
        Router $router,
        RouteConfig $config,
        MiddlewareChainInterface $preDispatchers = null,
        MiddlewareChainInterface $dispatcherChain = null,
        MiddlewareChainInterface $postDispatchers = null,
        ExceptionHandlerCollectionInterface $exceptionHandlerCollection = null
    )
    {
        $this->router = $router;
        $this->config = $config;
        $this->preDispatchers = $preDispatchers ?? new MiddlewareChain('pre');
        $this->dispatcherChain = $dispatcherChain ?? new MiddlewareChain('main');
        $this->postDispatchers = $postDispatchers ?? new MiddlewareChain('post');
        $this->exceptionHandlers = $exceptionHandlerCollection ?? new ExceptionHandlerCollection();
    }

    public function getPreDispatchChain() : MiddlewareChainInterface
    {
        return $this->preDispatchers;
    }

    public function getDispatchChain() : MiddlewareChainInterface
    {
        return $this->dispatcherChain;
    }

    public function getPostDispatchChain() : MiddlewareChainInterface
    {
        return $this->postDispatchers;
    }

    public function getExceptionHandlers() : ExceptionHandlerCollectionInterface
    {
        return $this->exceptionHandlers;
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

    public function lockMiddleware(): RouteInterface
    {
        $this->getPreDispatchChain()->lock();
        $this->getDispatchChain()->lock();
        $this->getPostDispatchChain()->lock();
        return $this;
    }

}