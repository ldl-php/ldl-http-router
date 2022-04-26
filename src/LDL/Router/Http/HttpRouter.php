<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Framework\Base\Constants;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollection;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollectionInterface;
use LDL\Router\Core\Route\Parsed\ParsedRoute;
use LDL\Router\Core\Route\Path\Parser\RoutePathParser;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Core\Traits\RouterInterfaceTrait;
use LDL\Router\Http\Dispatcher\HttpRouterDispatcherInterface;
use LDL\Router\Http\Dispatcher\HttpRouterRequestDispatcher;
use LDL\Router\Http\Exception\Handler\ExceptionHandlerInterface;
use LDL\Router\Http\Exception\Handler\HttpRouterExceptionHandler;
use LDL\Router\Http\Route\HttpRoute;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;

class HttpRouter implements HttpRouterInterface
{
    use RouterInterfaceTrait;

    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * @var HttpRouterDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        RouteCollectionInterface $routes,
        RoutePathParserInterface $parser = null,
        RouteCollector $routeCollector = null,
        ValidatorChainInterface $chain = null,
        HttpRouterDispatcherInterface $dispatcher = null,
        ExceptionHandlerInterface $handler = null
    ) {
        $this->_tRouterTraitRoutes = $routes;
        $this->_tRouterTraitParser = $parser ?? new RoutePathParser();
        $this->_tRouterTraitRouteCollector = $routeCollector ?? new RouteCollector();
        $this->_tRouterTraitValidatorChain = $chain ?? new AndValidatorChain();
        $this->dispatcher = $dispatcher ?? new HttpRouterRequestDispatcher();
        $this->exceptionHandler = $handler ?? new HttpRouterExceptionHandler();
    }

    public function getRouteList(): ParsedRouteCollectionInterface
    {
        $return = new ParsedRouteCollection();
        $collected = $this->_tRouterTraitRouteCollector->collect($this->_tRouterTraitRoutes);

        /**
         * @var CollectedRouteInterface $c
         */
        foreach ($collected as $c) {
            $path = $this->_tRouterTraitParser->parse(...$c->getPaths());
            /**
             * @var RouteInterface $route
             */
            $route = $c->getRoute();

            $return->append(
                new ParsedRoute(
                    $route->getName(),
                    $route->getDescription(),
                    $path->getPath(),
                    $route->getPath(),
                    $path->isDynamic(),
                    $path->getPlaceHolders(),
                    $route->getDispatchers(),
                    $route->getValidatorChain()
                )
            );
        }

        return $return;
    }

    public function findByRequest(RequestInterface $request): ?RoutePathMatchingResultInterface
    {
        $found = $this->find($request->getRequestUri());

        /**
         * @var RoutePathMatchingResultInterface $path
         */
        foreach ($found as $key => $path) {
            /**
             * @var HttpRoute $route
             */
            $route = $path->getCollectedRoute()->getRoute();

            try {
                $route->getValidatorChain()->validate($request);
            } catch (\Exception $e) {
                $found->removeByKey($key, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR);
            }
        }

        /**
         * Static routes have higher relevance since they provide us with an EXACT match
         * against the requested path.
         */
        $routes = $found->filterStatic();

        if (count($routes) > 0) {
            return $routes->get(0);
        }

        $routes = $found->filterDynamic();

        return count($routes) > 0 ? $routes->get(0) : null;
    }

    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response
    ): void {
        try {
            $this->dispatcher->dispatch($this, $request, $response);
        } catch (\Throwable $e) {
            $this->exceptionHandler->handle($e, $this, $request, $response);
        }
    }
}
