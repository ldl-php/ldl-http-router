<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config;

use LDL\Framework\Base\Traits\NameableTrait;

class MiddlewareConfig implements MiddlewareConfigInterface
{
    use NameableTrait;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var bool
     */
    private $storeInParameters;

    /**
     * @var bool
     */
    private $isPartOfResponse;

    /**
     * @var bool
     */
    private $isBlock;

    public function __construct(
        string $name,
        array $parameters = [],
        bool $storeInParameters = true,
        bool $isPartOfResponse = true,
        bool $isBlock = true
    )
    {
        $this->_tName = $name;
        $this->parameters = $parameters;
        $this->storeInParameters = $storeInParameters;
        $this->isPartOfResponse = $isPartOfResponse;
        $this->isBlock = $isBlock;
    }

    public function isPartOfResponseParameters() : bool
    {
        return $this->storeInParameters;
    }

    public function isPartOfResponse(): bool
    {
        return $this->isPartOfResponse;
    }

    public function getParameters() : array
    {
        return $this->parameters;
    }

    public function isBlocking(): bool
    {
        return $this->isBlock;
    }

}