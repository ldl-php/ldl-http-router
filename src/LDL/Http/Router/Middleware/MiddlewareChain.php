<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class MiddlewareChain extends ObjectCollection implements MiddlewareChainInterface
{
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

    public function getMiddleware(string $namespace, string $name): MiddlewareInterface
    {
        /**
         * @var MiddlewareInterface $middleware
         */
        foreach($this as $middleware){
            if($middleware->getNamespace() === $namespace && $middleware->getName() === $name){
                return $middleware;
            }
        }

        $msg = "Middleware with namespace: \"$namespace\" and name: \"$name\" could not be found";
        throw new Exception\MiddlewareNotFoundException($msg);
    }

    /**
     * @param MiddlewareInterface $item
     * @param null $key
     * @return CollectionInterface
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $key ?? \spl_object_hash($item));
    }

    /**
     * {@inheritdoc}
     */
    public function sort(string $order = 'asc'): MiddlewareChainInterface
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \LogicException('Order must be one of "asc" or "desc"');
        }

        $items = \iterator_to_array($this);

        usort(
            $items,
            /**
             * @var MiddlewareChain $a
             * @var MiddlewareChain $b
             *
             * @return bool
             */
            static function ($a, $b) use ($order) {
                $prioA = $a->getPriority();
                $prioB = $b->getPriority();

                return 'asc' === $order ? $prioA <=> $prioB : $prioB <=> $prioA;
            }
        );

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : array
    {
        $this->isDispatched = true;
        $return = [];

        /**
         * @var MiddlewareInterface $dispatch
         */
        foreach ($this->sort('asc') as $dispatch) {
            if (false === $dispatch->isActive()) {
                continue;
            }

            $result = $dispatch->dispatch(
                $route,
                $request,
                $response,
                $urlArgs
            );

            $this->lastExecuted = $dispatch;

            if(null !== $result){
                $return[$dispatch->getNamespace()] = [
                    $dispatch->getName() => $result
                ];
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                break;
            }

        }

        $this->result = $return;

        return $return;
    }

    public function validateItem($item): void
    {
        parent::validateItem($item);

        if ($item instanceof MiddlewareInterface) {
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            MiddlewareInterface::class
        );

        throw new TypeMismatchException($msg);
    }

}
