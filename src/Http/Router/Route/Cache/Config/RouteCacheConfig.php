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
    private $purgeable;

    /**
     * @var bool
     */
    private $enabled=true;

    public static function fromArray(array $data) : self
    {
        $merge = array_merge(get_class_vars(__CLASS__), $data);

        return new static(
            (bool) $merge['purgeable'],
            (bool) $merge['enabled'],
            $merge['expiresAt'],
            $merge['secretKey']
        );
    }

    public function __construct(
        bool $purgeable,
        bool $enabled=true,
        ?string $expiresAt=null,
        ?string $secretKey=null
    )
    {
        if(null !== $expiresAt){
            $expiresAt = \DateInterval::createFromDateString($expiresAt);
        }

        $this->setEnabled($enabled)
            ->setExpiresAt($expiresAt)
            ->setSecretKey($secretKey)
            ->setPurgeable($purgeable);
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
        return $this->purgeable;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    private function setEnabled(bool $enabled) : self
    {
        $this->enabled = $enabled;
        return $this;
    }

    private function setPurgeable(bool $purgeable) : self
    {
        $this->purgeable = $purgeable;
        return $this;
    }

    private function setExpiresAt(\DateInterval $interval) : self
    {
        $this->expiresAt = $interval;
        return $this;
    }

    private function setSecretKey(string $key=null) : self
    {
        $this->secretKey = $key;
        return $this;
    }

}