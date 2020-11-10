<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

class ExceptionHandlerCollection extends ObjectCollection implements ExceptionHandlerCollectionInterface
{
    /**
     * @var ExceptionHandlerInterface|null
     */
    private $lastExecutedExceptionHandler;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ExceptionHandlerInterface::class))
            ->lock();
    }

    /**
     * @param ExceptionHandlerInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function getLastExecutedExceptionHandler(): ?ExceptionHandlerInterface
    {
        return $this->lastExecutedExceptionHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        Router $router,
        \Exception $e,
        ParameterBag $urlParameters=null
    ) : array
    {
        if(0 === count($this)){
            throw $e;
        }

        $response = $router->getResponse();

        /**
         * @var ExceptionHandlerInterface $exceptionHandler
         */
        foreach($this as $exceptionHandler){
            $this->lastExecutedExceptionHandler = $exceptionHandler;

            $httpStatusCode = $exceptionHandler->handle($router, $e, $urlParameters);

            if(null === $httpStatusCode){
                continue;
            }

            $response->setStatusCode($httpStatusCode);

            $modifiesResponse = $exceptionHandler instanceof ModifiesResponseInterface;

            return $modifiesResponse ? $exceptionHandler->getContent() : ['error' => $e->getMessage()];
        }

        throw $e;
    }
}