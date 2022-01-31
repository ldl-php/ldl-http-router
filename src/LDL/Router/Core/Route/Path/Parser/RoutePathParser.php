<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Parser;

use LDL\Framework\Helper\RegexHelper;
use LDL\Router\Core\Route\Collector\CollectedRoute;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollection;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResult;
use LDL\Router\Core\Route\Path\Result\RoutePathParsingResult;
use LDL\Router\Core\Route\Path\Result\RoutePathParsingResultInterface;

class RoutePathParser implements RoutePathParserInterface
{
    /**
     * @var string
     */
    private $pathSeparator;

    public function __construct(string $pathSeparator = '/')
    {
        $this->pathSeparator = $pathSeparator;
    }

    public function getPathSeparator(): string
    {
        return $this->pathSeparator;
    }

    public function normalize(string ...$paths): string
    {
        return implode($this->pathSeparator, array_filter(
            explode($this->pathSeparator, implode($this->pathSeparator, $paths)),
            static function ($p) {
                return '' !== $p;
            })
        );
    }

    public function parse(string ...$paths): RoutePathParsingResultInterface
    {
        $normalizedPath = array_filter(
            explode($this->pathSeparator, implode($this->pathSeparator, $paths)),
            static function ($p) {
                return '' !== $p;
            });

        $isDynamic = false;

        foreach ($normalizedPath as $piece) {
            $isDynamic = (bool) preg_match('#:#', $piece);
        }

        $separator = $isDynamic ? preg_quote($this->pathSeparator, $this->pathSeparator) : $this->pathSeparator;
        $placeHolders = [];

        $path = implode($separator, array_map(function (string $piece) use (&$placeHolders) {
            $piece = trim($piece, preg_quote($this->pathSeparator, $this->pathSeparator));

            /*
             * Not a dynamic place holder
             */
            if (false === strpos($piece, ':')) {
                return $piece;
            }

            $parts = explode(':', $piece);
            $placeHolders[] = $parts[1];
            $regex = '' === $parts[0] ? '([\w|\d]+)' : sprintf('(%s)', $parts[0]);

            RegexHelper::validate($regex);

            return $regex;
        }, $normalizedPath));

        return new RoutePathParsingResult(
            $path,
            $isDynamic,
            $placeHolders
        );
    }

    public function match(string $requestedPath, iterable $collected): RoutePathMatchingCollectionInterface
    {
        $return = new RoutePathMatchingCollection();
        $requestedPath = trim($requestedPath, $this->pathSeparator);

        /**
         * @var CollectedRoute $cr
         */
        foreach ($collected as $cr) {
            $path = $this->parse(...$cr->getPaths());
            /*
             * Static routes are of higher importance than dynamic routes since they provide an EXACT match
             */
            if (
                !$path->isDynamic() &&
                trim($cr->getRoute()->getPath(), $this->pathSeparator) === $requestedPath
            ) {
                $return->append(
                    new RoutePathMatchingResult($path, $cr->getRoute(), [])
                );
                continue;
            }

            $matches = [];

            preg_match("#^{$path->getPath()}$#", $requestedPath, $matches);

            if (!array_key_exists(0, $matches)) {
                continue;
            }

            unset($matches[0]);

            if (0 === count($matches)) {
                continue;
            }
            $return->append(
                new RoutePathMatchingResult(
                    $path,
                    $cr->getRoute(),
                    array_combine($path->getPlaceHolders(), $matches)
                )
            );
        }

        return $return;
    }
}
