<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

class ExceptionHandlerCollection extends ObjectCollection implements ExceptionHandlerCollectionInterface
{
    use NamespaceableTrait;
    use PrioritySortingTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ExceptionHandlerInterface::class))
            ->lock();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        Router $router,
        \Exception $e,
        string $context,
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

            if(false === $exceptionHandler->isActive()){
                continue;
            }

            $httpStatusCode = $exceptionHandler->handle($router, $e, $context, $urlParameters);

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