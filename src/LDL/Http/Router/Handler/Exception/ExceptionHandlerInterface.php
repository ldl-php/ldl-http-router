<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ExceptionHandlerInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param Router $router
     * @param \Exception $e
     * @param string $context
     * @param ParameterBag $urlParameters
     *
     * @return int|null
     */
    public function handle(
        Router $router,
        \Exception $e,
        string $context,
        ParameterBag $urlParameters=null
    ) : ?int;
}