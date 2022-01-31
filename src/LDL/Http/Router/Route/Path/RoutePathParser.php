<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path;

use LDL\Framework\Helper\RegexHelper;
use LDL\Http\Router\Route\Path\Result\RoutePathMatchingResult;
use LDL\Http\Router\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Http\Router\Route\Path\Result\RoutePathParsingResult;
use LDL\Http\Router\Route\Path\Result\RoutePathParsingResultInterface;
use LDL\Http\Router\Route\RouteInterface;

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

    public function parse(RouteInterface $route, string ...$paths): RoutePathParsingResultInterface
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
            $route,
            $path,
            $isDynamic,
            $placeHolders
        );
    }

    public function match(
        string $requestedPath,
        RoutePathParsingResultInterface $result
    ): ?RoutePathMatchingResultInterface {
        $requestedPath = trim($requestedPath, $this->pathSeparator);

        if (!$result->isDynamic() && trim($result->getRoute()->getPath(), $this->pathSeparator) === $requestedPath) {
            return new RoutePathMatchingResult($result, null);
        }

        $path = $result->getPath();
        $matches = [];

        preg_match("#^$path$#", $requestedPath, $matches);

        if (!array_key_exists(0, $matches)) {
            return null;
        }

        unset($matches[0]);

        if (0 === count($matches)) {
            return null;
        }

        return new RoutePathMatchingResult(
            $result,
            array_combine($result->getPlaceHolders(), $matches)
        );
    }
}
