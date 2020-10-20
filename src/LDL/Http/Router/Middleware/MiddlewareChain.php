<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Filter\FilterByActiveStateTrait;
use LDL\Type\Collection\Traits\Filter\FilterByInterfaceTrait;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

class MiddlewareChain extends ObjectCollection implements MiddlewareChainInterface
{

    use NamespaceableTrait;
    use ValueValidatorChainTrait;
    use PrioritySortingTrait;
    use FilterByInterfaceTrait;
    use FilterByActiveStateTrait;

    /**
     * @var MiddlewareInterface
     */
    private $lastExecuted;

    /**
     * @var bool
     */
    private $isDispatched = false;

    /**
     * @var array
     */
    private $result = [];

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareInterface::class))
            ->lock();
    }

    /**
     * {@inheritdoc}
     */
    public function getResult() : array
    {
        if(!$this->isDispatched){
            $msg = 'You can not obtain the result of an "undispatched" middleware chain';
            throw new Exception\UndispatchedMiddlewareChainException($msg);
        }

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function isDispatched() : bool
    {
        return $this->isDispatched;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastExecutedDispatcher() : MiddlewareInterface
    {
        if(false === $this->isDispatched){
            $msg = 'You can not the last executed dispatcher of an "undispatched" middleware chain';
            throw new Exception\UndispatchedMiddlewareChainException($msg);
        }

        return $this->lastExecuted;
    }

    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $key ?? \spl_object_hash($item));
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : array
    {
        $this->isDispatched = true;
        $this->result = [];

        /**
         * @var MiddlewareInterface $dispatch
         */
        foreach ($this as $dispatch) {

            $result = $dispatch->dispatch(
                $route,
                $request,
                $response,
                $urlParameters
            );

            $this->lastExecuted = $dispatch;

            if(null !== $result){
                $this->result[$dispatch->getNamespace()][$dispatch->getName()] = $result;
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                break;
            }
        }

        return $this->result;
    }

}
