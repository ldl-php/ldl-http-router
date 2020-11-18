<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ExceptionHandlerCollectionInterface extends CollectionInterface, HasKeyValidatorChainInterface
{
    /**
     * @param Router $router
     * @param \Exception $exception
     * @param ParameterBag $urlParameters
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function handle(
        Router $router,
        \Exception $exception,
        ParameterBag $urlParameters=null
    ) : ?array;

    /**
     * Return the last executed exception handler
     *
     * @return ExceptionHandlerInterface|null
     */
    public function getLastExecutedExceptionHandler() : ?ExceptionHandlerInterface;
}
