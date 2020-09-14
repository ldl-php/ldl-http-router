<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\FinalDispatcher;
use LDL\Http\Router\Route\Route;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class PostDispatchMiddlewareCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if ($item instanceof PostDispatchMiddlewareInterface) {
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            PostDispatchMiddlewareInterface::class
        );

        throw new TypeMismatchException($msg);
    }

    /**
     * @param PostDispatchMiddlewareInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $key ?? \spl_object_hash($item));
    }

    public function sort(string $order = 'asc'): self
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \LogicException('Order must be one of "asc" or "desc"');
        }

        $items = \iterator_to_array($this);

        usort(
            $items,
            /**
             * @var PostDispatchMiddlewareInterface $a
             * @var PostDispatchMiddlewareInterface $b
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

    public function dispatchFinal(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $result
    ) : array
    {
        /**
         * @var PostDispatchMiddlewareInterface $postDispatch
         */
        foreach ($this->sort('asc') as $postDispatch) {
            if (false === $postDispatch->isActive()) {
                continue;
            }

            if(!$postDispatch instanceof FinalDispatcher){
                continue;
            }

            $postDispatch->dispatch(
                $route,
                $request,
                $response,
                $result
            );

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                break;
            }
        }

        return $result;

    }



    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response
    ) : array
    {
        $result = [];

        /**
         * @var PostDispatchMiddlewareInterface $postDispatch
         */
        foreach ($this->sort('asc') as $postDispatch) {
            if (false === $postDispatch->isActive()) {
                continue;
            }

            if($postDispatch instanceof FinalDispatcher){
                continue;
            }

            $postResult = $postDispatch->dispatch(
                $route,
                $request,
                $response,
                $result
            );

            if(null !== $postResult){
                $result[$postDispatch->getNamespace()] = [
                    $postDispatch->getName() => $postResult
                ];
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                break;
            }

        }

        return $result;

    }

}
