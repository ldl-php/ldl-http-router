<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Collection;

use LDL\Framework\Base\Collection\Contracts\CollectionInterface;
use LDL\Framework\Base\Constants;
use LDL\Http\Router\Route\Collection\Exception\DuplicateRouteException;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Http\Router\Route\Path\Result\RoutePathParsingResultInterface;
use LDL\Http\Router\Route\Path\RoutePathParser;
use LDL\Http\Router\Route\Path\RoutePathParserInterface;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class RouteCollection extends AbstractTypedCollection implements RouteCollectionInterface
{
    /**
     * @var RoutePathParserInterface
     */
    private $pathParser;

    public function __construct(iterable $items = null, RoutePathParserInterface $pathParser = null)
    {
        $this->pathParser = $pathParser ?? new RoutePathParser();

        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RoutePathParsingResultInterface::class))
            ->lock();

        $this->getBeforeResolveKey()->append(static function (
            RouteCollectionInterface $collection,
            RoutePathParsingResultInterface $item,
            string $key
        ) {
            if (!$collection->hasKey($key, Constants::OPERATOR_SEQ, Constants::COMPARE_LTR)) {
                return;
            }

            /**
             * @var RoutePathParsingResultInterface $path
             */
            foreach ($collection as $path) {
                if ($path->getPath() !== $key) {
                    continue;
                }

                throw new DuplicateRouteException(sprintf('The route named: %s with path: %s, conflicts with route named: %s, with path: %s', $item->getRoute()->getName(), $item->getRoute()->getPath(), $path->getRoute()->getName(), $path->getRoute()->getPath()));
            }
        });

        parent::__construct($items);
    }

    public function append($route, $key = null): CollectionInterface
    {
        if ($route instanceof RouteGroupInterface) {
            return $this->addGroup($route);
        }

        if ($route instanceof RouteInterface) {
            return $this->addRoute($route);
        }

        return parent::append($route, $key);
    }

    public function addRoute(RouteInterface $route): RouteCollectionInterface
    {
        $result = $this->pathParser->parse($route, $route->getPath());
        parent::append($result, $result->getPath());

        return $this;
    }

    public function addGroup(RouteGroupInterface $group): RouteCollectionInterface
    {
        /**
         * @var RouteInterface $route
         */
        foreach ($group as $route) {
            $result = $this->pathParser->parse($route, $group->getPath(), $route->getPath());
            parent::append($result, $result->getPath());
        }

        return $this;
    }

    public function filterStaticRoutes()
    {
        return $this->filter(static function (RoutePathParsingResultInterface $path) {
            return !$path->isDynamic();
        });
    }

    public function filterDynamicRoutes()
    {
        return $this->filter(static function (RoutePathParsingResultInterface $path) {
            return $path->isDynamic();
        });
    }

    public function match(string $requestedPath): ?RoutePathMatchingResultInterface
    {
        /*
         * Static routes have higher importance than dynamic ones
         */
        foreach ($this->filterStaticRoutes() as $path => $route) {
            $matched = $this->pathParser->match($requestedPath, $route);

            if ($matched) {
                return $matched;
            }
        }

        /*
         * Dynamic routes have lower importance than dynamic ones
         */
        foreach ($this->filterDynamicRoutes() as $path => $route) {
            $matched = $this->pathParser->match($requestedPath, $route);

            if ($matched) {
                return $matched;
            }
        }

        return null;
    }
}
