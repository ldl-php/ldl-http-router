<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Exception\LockingException;
use LDL\Framework\Base\Traits\IsActiveInterfaceTrait;
use LDL\Framework\Base\Traits\LockableObjectInterfaceTrait;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\RouteInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    use LockableObjectInterfaceTrait;
    use IsActiveInterfaceTrait;

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

    public function __construct(string $name)
    {
        $this->name = $name;
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

    /**
     * {@inheritdoc}
     */
    final public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $urlParameters=null
    ) : void
    {
        if(false === $this->_tActive){
            return;
        }

        $this->result = [];
        $this->isDispatched = true;
        $this->result = $this->_dispatch($request, $response, $route, $urlParameters);
    }

    /**
     * @param RouteInterface $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag|null $urlParameters
     * @return mixed
     */
    abstract protected function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $urlParameters=null
    );
}
