<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Cache;

use LDL\Http\Core\Request\RequestInterface;

interface CacheableInterface
{
    /**
     * @param RequestInterface $request
     * @return string
     */
    public function getCacheKey(RequestInterface $request) : string;
}