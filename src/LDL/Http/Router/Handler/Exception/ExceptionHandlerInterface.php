<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Framework\Contracts\IsActiveInterface;
use LDL\Framework\Contracts\NamespaceInterface;
use LDL\Framework\Contracts\PriorityInterface;
use LDL\Http\Router\Router;

interface ExceptionHandlerInterface extends NamespaceInterface, PriorityInterface, IsActiveInterface
{
    /**
     * @param Router $router
     * @param \Exception $e
     * @param string $context
     * @return int|null
     */
    public function handle(Router $router, \Exception $e, string $context) : ?int;
}