<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Http\Router\Router;

interface ExceptionHandlerInterface
{
    /**
     * @return string
     */
    public function getNamespace() : string;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return int
     */
    public function getPriority() : int;

    /**
     * @return bool
     */
    public function isActive() : bool;

    /**
     * @param Router $router
     * @param \Exception $e
     * @return int|null
     */
    public function handle(Router $router, \Exception $e) : ?int;
}