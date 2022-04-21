<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Framework\Base\Constants;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollection;
use LDL\Router\Core\Route\Parsed\Collection\ParsedRouteCollectionInterface;
use LDL\Router\Core\Route\Parsed\ParsedRoute;
use LDL\Router\Core\Route\Path\Parser\RoutePathParser;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\Route\RouteInterface;
use LDL\Router\Core\Traits\RouterInterfaceTrait;
use LDL\Router\Http\Dispatcher\DefaultHttpRouterRequestDispatcherInterface;
use LDL\Router\Http\Dispatcher\HttpRouterDispatcherInterface;
use LDL\Router\Http\Route\HttpRoute;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\ValidatorChainInterface;

class HttpRouter implements HttpRouterInterface
{
    use RouterInterfaceTrait;

    /**
     * @var HttpRouterDispatcherInterface
     */
    private $requestDispatcher;

    public function __construct(
        RouteCollectionInterface $routes,
        HttpRouterDispatcherInterface $requestDispatcher = null,
        RoutePathParserInterface $parser = null,
        RouteCollector $routeCollector = null,
        ValidatorChainInterface $chain = null
    ) {
        $this->_tRouterTraitRoutes = $routes;
        $this->_tRouterTraitParser = $parser ?? new RoutePathParser();
        $this->_tRouterTraitRouteCollector = $routeCollector ?? new RouteCollector();
        $this->_tRouterTraitValidatorChain = $chain ?? new AndValidatorChain();
        $this->requestDispatcher = $requestDispatcher ?? new DefaultHttpRouterRequestDispatcherInterface();
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

    public function findByRequest(RequestInterface $request): RoutePathMatchingCollectionInterface
    {
        $found = $this->find($request->getRequestUri());

        /**
         * @var RoutePathMatchingResultInterface $f
         */
        foreach ($found as $key => $f) {
            /**
             * @var HttpRoute $route
             */
            $route = $f->getCollectedRoute()->getRoute();

            try {
                $route->getValidatorChain()->validate($request);
            } catch (\Exception $e) {
                $found->removeByKey($key, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR);
            }
        }

        return $found;
    }

    public function dispatch(RequestInterface $request): RouteDispatcherResultCollectionInterface
    {
        return $this->requestDispatcher->dispatch($this, $request);
    }
}
