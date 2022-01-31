<?php

declare(strict_types=1);

namespace LDL\Router\Http;

use LDL\Framework\Base\Constants;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Collector\RouteCollector;
use LDL\Router\Core\Route\Path\Parser\RoutePathParserInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Router\Core\Router;
use LDL\Router\Http\Dispatcher\DefaultHttpRouterRequestDispatcher;
use LDL\Router\Http\Dispatcher\HttpRouterDispatcher;
use LDL\Router\Http\Route\HttpRoute;
use LDL\Validators\Chain\ValidatorChainInterface;

class HttpRouter extends Router implements HttpRouterInterface
{
    /**
     * @var HttpRouterDispatcher
     */
    private $requestDispatcher;

    public function __construct(
        RouteCollectionInterface $routes,
        HttpRouterDispatcher $requestDispatcher = null,
        RoutePathParserInterface $parser = null,
        RouteCollector $routeCollector = null,
        ValidatorChainInterface $chain = null
    ) {
        $this->requestDispatcher = $requestDispatcher ?? new DefaultHttpRouterRequestDispatcher();
        parent::__construct($routes, $parser, $routeCollector, $chain);
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
            $route = $f->getRoute();

            try {
                $route->getValidatorChain()->validate($request);
            } catch (\Exception $e) {
                $found->removeByKey($key, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR);
            }
        }

        return $found;
    }

    public function dispatch(RequestInterface $request): iterable
    {
        return $this->requestDispatcher->dispatch($this, $request);
    }
}
