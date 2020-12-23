<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Exception\LockingException;
use LDL\Framework\Base\Traits\IsActiveInterfaceTrait;
use LDL\Framework\Base\Traits\LockableObjectInterfaceTrait;
use LDL\Http\Router\Middleware\Config\Factory\MiddlewareConfigFactory;
use LDL\Http\Router\Validator\Request\Traits\RequestValidatorChainTrait;

trait MiddlewareTrait
{
    use LockableObjectInterfaceTrait;
    use IsActiveInterfaceTrait;
    use RequestValidatorChainTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isDispatched = false;

    /**
     * @var array
     */
    private $result;

    /**
     * @var int|null
     */
    private $priority;

    /**
     * @var Config\MiddlewareConfigInterface
     */
    private $config;

    public function __construct(string $name, array $config=[])
    {
        $this->name = $name;
        $this->config = count($config) === 0 ? new Config\MiddlewareConfig($name) : MiddlewareConfigFactory::fromArray($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function getResult()
    {
        if(!$this->isDispatched){
            $msg = 'You can not obtain the result of an "undispatched" middleware';
            throw new Exception\UndispatchedMiddlewareException($msg);
        }

        return $this->result;
    }

    public function isDispatched(): bool
    {
        return $this->isDispatched;
    }

    public function setActive(bool $isActive) : MiddlewareInterface
    {
        if($this->isLocked()){
            $msg = "Middleware '{$this->name}' is locked and can not be modified";
            throw new LockingException($msg);
        }

        $this->_tActive = $isActive;

        return $this;
    }

    public function setPriority(int $priority) : MiddlewareInterface
    {
        if($this->isLocked()){
            $msg = "Middleware '{$this->name}' is locked and can not be modified";
            throw new LockingException($msg);
        }

        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getConfig() : Config\MiddlewareConfigInterface
    {
        return $this->config;
    }

}
