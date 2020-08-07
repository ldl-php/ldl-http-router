<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Cache\Config;

class RouteCacheConfig
{
    /**
     * @var \DateInterval
     */
    private $expiresAt;

    /**
     * @var string
     */
    private $cacheKey;

    public function __construct(
        string $cacheKey,
        string $expiresAt=null
    )
    {
        if(null !== $expiresAt){
            $expiresAt = \DateInterval::createFromDateString($expiresAt);
        }

        $this->cacheKey = $cacheKey;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return \DateInterval
     */
    public function getExpiresAt() : ?\DateInterval
    {
        return $this->expiresAt;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->cacheKey;
    }

}