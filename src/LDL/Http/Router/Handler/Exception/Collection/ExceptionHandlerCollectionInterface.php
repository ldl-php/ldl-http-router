<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ExceptionHandlerCollectionInterface extends CollectionInterface
{
    /**
     * @param Router $router
     * @param \Exception $exception
     * @param string $context
     * @param ParameterBag $urlParameters
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function handle(
        Router $router,
        \Exception $exception,
        string $context,
        ParameterBag $urlParameters=null
    ) : array;
}
