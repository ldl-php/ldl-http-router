<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Framework\Base\Constants;
use LDL\Framework\Helper\IterableHelper;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Path\Parser\RoutePathParser;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\Traits\RouterInterfaceTrait;
use LDL\Router\Http\Dispatcher\DefaultHttpRouterRequestDispatcherInterface;
use LDL\Router\Http\Dispatcher\HttpRouterDispatcherInterface;
use LDL\Router\Http\Route\HttpRoute;
use LDL\Router\Http\Route\HttpRouteInterface;
use LDL\Validators\Chain\AndValidatorChain;
use LDL\Validators\Chain\Dumper\ValidatorChainHumanDumper;
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

    public function getRouteList(): array
    {
        $return = [];
        $collected = $this->_tRouterTraitRouteCollector->collect($this->_tRouterTraitRoutes);

        /**
         * @var CollectedRouteInterface $c
         */
        foreach ($collected as $c) {
            $path = $this->_tRouterTraitParser->parse(...$c->getPaths());
            /**
             * @var HttpRouteInterface $route
             */
            $route = $c->getRoute();

            $return[] = [
                'name' => $route->getName(),
                'description' => $route->getDescription(),
                'path' => [
                    'parsed' => $path->getPath(),
                    'original' => $route->getPath(),
                ],
                'dynamic' => $path->isDynamic(),
                'placeholders' => $path->getPlaceHolders(),
                'methods' => $route->getMethods()->toArray(),
                'dispatchers' => IterableHelper::map($route->getDispatchers(), static function ($d) {
                    return get_class($d);
                }),
                'validators' => ValidatorChainHumanDumper::dump($route->getValidatorChain()),
            ];
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
