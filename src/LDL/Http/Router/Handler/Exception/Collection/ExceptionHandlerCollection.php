<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

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
        \Exception $exception,
        string $context
    ) : void
    {
        if(0 === count($this)){
            throw $exception;
        }

        $response = $router->getResponse();

        $currentRoute = $router->getCurrentRoute();

        $defaultResponseParser = $router->getResponseParserRepository()->getLast();
        $parser = $currentRoute ? $currentRoute->getConfig()->getResponseParser() : $defaultResponseParser;

        /**
         * @var ExceptionHandlerInterface $exceptionHandler
         */
        foreach($this as $exceptionHandler){

            if(false === $exceptionHandler->isActive()){
                continue;
            }

            $httpStatusCode = $exceptionHandler->handle($router, $exception, $context);

            if(null === $httpStatusCode) {
                continue;
            }

            $response->setStatusCode($httpStatusCode);

            $response->setContent(
                $parser ? $parser->parse(
                    [
                        'error' => $exception->getMessage()
                    ],
                    $context,
                    $router
                ) : $exception->getMessage()
            );

            return;
        }

        throw $exception;
    }
}