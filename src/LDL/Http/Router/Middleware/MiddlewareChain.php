<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Exception\LockingException;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepositoryInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultItem;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepositoryInterface;
use LDL\Http\Router\Middleware\Dispatcher\MiddlewareDispatcherInterface;
use LDL\Http\Router\Route\Parameter\RouteParameterInterface;
use LDL\Http\Router\Route\Parameter\RouteParameters;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Filter\FilterByActiveStateTrait;
use LDL\Type\Collection\Traits\Filter\FilterByInterfaceTrait;
use LDL\Type\Collection\Traits\Selection\MultipleSelectionTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Types\String\StringCollection;
use LDL\Type\Collection\Validator\UniqueValidator;
use Psr\EventDispatcher\EventDispatcherInterface;

class MiddlewareChain extends ObjectCollection implements MiddlewareChainInterface
{
    use KeyValidatorChainTrait;
    use ValueValidatorChainTrait;
    use PrioritySortingTrait;
    use FilterByInterfaceTrait;
    use FilterByActiveStateTrait;
    use MultipleSelectionTrait;

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

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareInterface::class, false))
            ->append(new InterfaceComplianceItemValidator(MiddlewareChainInterface::class, false))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
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
            $msg = 'You can not obtain the last executed dispatcher of an "undispatched" middleware chain';
            throw new Exception\UndispatchedMiddlewareException($msg);
        }

        return $this->lastExecuted;
    }

    /**
     * @param MiddlewareInterface $item
     * @param null $key
     * @return MiddlewareChainInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function getLastException() : ?\Exception
    {
        return $this->lastException;
    }

    public function getRequiredParameters(): StringCollection
    {
        return new StringCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(
        string $context,
        EventDispatcherInterface $events,
        RouterContainerInterface $sources,
        MiddlewareConfigRepositoryInterface $configRepository,
        ExceptionHandlerRepositoryInterface $exceptionHandlers
    ) : MiddlewareChainInterface
    {
        $this->isDispatched = true;

        /**
         * @var MiddlewareInterface|MiddlewareDispatcherInterface $dispatch
         */
        foreach ($this as $dispatch) {
            if(false === $dispatch->isActive()){
                continue;
            }

            $this->lastExecuted = $dispatch;

            /**
             * There could be two possible situations, the dispatcher is a simple regular dispatcher
             * or a collection of dispatchers.
             */
            if($dispatch instanceof MiddlewareChainInterface){
                $dispatch->dispatch(
                    $context,
                    $events,
                    $sources,
                    $configRepository,
                    $exceptionHandlers,
                    $events
                );

                continue;
            }

            $dispatcherConfig = $configRepository->get($dispatch->getName());

            $context = sprintf(
                '%s%s.%s',
                $this->name ?? '',
                $context,
                $dispatcherConfig->getName()
            );

            $pass = [];

            /**
             * @var RouteParameterInterface $param
             */
            foreach(RouteParameters::create($dispatcherConfig->getParameters()) as $param){
                /**
                 * Obtain parameters from the sources configured in the dispatcher configuration
                 */
                if($param->getResolver()) {
                    $pass[] = $sources->getResolved(
                        $param->getSource(),
                        $param->getResolver(),
                        $param->getName()
                    );

                    continue;
                }

                $pass[] = $sources->get($param->getSource(), $param->getName());
            }

            $events->dispatch(
                new Event\MiddlewareDispatchBeforeEvent(
                    sprintf('%s.%s',$context, 'before'),
                    $dispatch,
                    $dispatcherConfig,
                    $pass
                )
            );

            try {

                $return = count($pass) > 0 ? $dispatch->dispatch(...$pass) : $dispatch->dispatch();

            }catch(\Exception $e){

                if($dispatcherConfig->isBlocking()){
                    throw $e;
                }

                $return = $dispatcherConfig->isPartOfResponse() ? $exceptionHandlers->handle($e) : null;
            }

            /**
             * if it's not part of the response then continue
             */
            if(false === $dispatcherConfig->isPartOfResponse()) {
                continue;
            }

            $resultItem = new MiddlewareChainResultItem(
                $context,
                $dispatch,
                $dispatcherConfig,
                $return
            );

            $events->dispatch(
                new Event\MiddlewareDispatchAfterEvent(
                    sprintf('%s.%s',$context, 'after'),
                    $resultItem
                )
            );

            $sources->getResponseResult()->append($resultItem, $context);
        }

        return $this;
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
