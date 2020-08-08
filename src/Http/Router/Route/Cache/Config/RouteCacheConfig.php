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
    private $secretKey;

    /**
     * @var bool
     */
    private $isPurgeable;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct(
        bool $isPurgeable,
        bool $enabled=true,
        ?string $expiresAt=null,
        ?string $secretKey=null
    )
    {
        if(null !== $expiresAt){
            $expiresAt = \DateInterval::createFromDateString($expiresAt);
        }

        $this->enabled = $enabled;
        $this->secretKey = $secretKey;
        $this->expiresAt = $expiresAt;
        $this->isPurgeable = $isPurgeable;
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
    public function getSecretKey() : ?string
    {
        return $this->secretKey;
    }

    public function isPurgeable() : bool
    {
        return $this->isPurgeable;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

}