<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Exception\LockingException;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Filter\FilterByActiveStateTrait;
use LDL\Type\Collection\Traits\Filter\FilterByInterfaceTrait;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

class MiddlewareChain extends ObjectCollection implements MiddlewareChainInterface
{
    use KeyValidatorChainTrait;
    use NamespaceableTrait;
    use ValueValidatorChainTrait;
    use PrioritySortingTrait;
    use FilterByInterfaceTrait;
    use FilterByActiveStateTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var MiddlewareInterface
     */
    private $lastExecuted;

    /**
     * @var bool
     */
    private $isDispatched = false;

    /**
     * @var int|null
     */
    private $priority;

    /**
     * @var array
     */
    private $result;

    /**
     * @var \Exception|null
     */
    private $lastException;

    /**
     * @var bool
     */
    private $isActive;

    public function __construct(
        string $name = null,
        bool $isActive = true,
        iterable $items = null
    )
    {
        parent::__construct($items);
        $this->name = $name;
        $this->isActive = $isActive;

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueKeyValidator())
            ->lock();
    }

    public function isDispatched(): bool
    {
        return $this->isDispatched;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive) : MiddlewareInterface
    {
        if($this->isLocked()){
            $msg = "Middleware collection '{$this->name}' is locked and can not be modified";
            throw new LockingException($msg);
        }

        $this->isActive = $isActive;

        return $this;
    }

    public function setPriority(int $priority) : MiddlewareInterface
    {
        if($this->isLocked()){
            $msg = "Middleware collection '{$this->name}' is locked and can not be modified";
            throw new LockingException($msg);
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastExecutedDispatcher() : MiddlewareInterface
    {
        if(false === $this->isDispatched){
            $msg = 'You can not the last executed dispatcher of an "undispatched" middleware chain';
            throw new Exception\UndispatchedMiddlewareException($msg);
        }

        return $this->lastExecuted;
    }

    /**
     * @param MiddlewareInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        $priority = $item->getPriority();
        return parent::append($item, $priority ?? count($this) + 1);
    }

    public function getLastException() : ?\Exception
    {
        return $this->lastException;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParameters=null
    ) : void
    {
        $this->result = null;
        $this->isDispatched = true;

        /**
         * @var MiddlewareInterface $dispatch
         */
        foreach ($this as $dispatch) {
            if(false === $dispatch->isActive()){
                continue;
            }

            try {
                $this->lastExecuted = $dispatch;

                $dispatch->dispatch(
                    $request,
                    $response,
                    $router,
                    $urlParameters
                );

                $result = $dispatch->getResult();

                if (null !== $result) {
                    $this->appendToResult($result, $dispatch->getName());
                }

                $httpStatusCode = $response->getStatusCode();

                if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK) {
                    break;
                }
            }catch(\Exception $e){
                $result = $dispatch->getResult();

                if (null !== $result) {
                    $this->appendToResult($result, $dispatch->getName());
                }

                $this->parseException($router, $e);

                $this->lastException = $e;

                //throw $e;
            }

        }
    }

    private function appendToResult($data, string $name = null) : void
    {
        if(null === $this->result){
            $this->result = [];
        }

        if(null === $name){
            $this->result[] = $data;
            return;
        }

        $this->result[$name] = $data;
    }


    private function parseException(
        Router $router,
        \Exception $e
    ) : void
    {
        $lastExecutedDispatcher = $this->lastExecuted;
        $resultKey = $lastExecutedDispatcher->getName();
        $route = $router->getCurrentRoute();
        $routerHandlers = $router->getExceptionHandlerCollection();

        if(null === $route){
            $exception = $routerHandlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception) {
                $this->appendToResult($exception, $resultKey);
            }

            return;
        }

        try{

            $handlers = $route->getExceptionHandlers();

            $exception = $handlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception) {
                $this->appendToResult($exception, $resultKey);
            }

        }catch (\Exception $e){

            $exception = $routerHandlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception){
                $this->appendToResult($exception, $resultKey);
            }

        }

    }

    /**
     * {@inheritdoc}
     */
    public function getResult() : ?array
    {
        if(!$this->isDispatched){
            $msg = 'You can not obtain the result of an "undispatched" middleware chain';
            throw new Exception\UndispatchedMiddlewareException($msg);
        }

        return $this->result;
    }
}
