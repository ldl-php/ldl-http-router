<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Framework\Base\Traits\IsActiveInterfaceTrait;
use LDL\Framework\Base\Traits\NamespaceInterfaceTrait;
use LDL\Framework\Base\Traits\PriorityInterfaceTrait;

abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    use NamespaceInterfaceTrait;
    use PriorityInterfaceTrait;
    use IsActiveInterfaceTrait;

    public function __construct(
        string $namespace,
        string $name,
        int $priority,
        bool $isActive
    )
    {
        $this->_tNamespace = $namespace;
        $this->_tName = $name;
        $this->_tActive = $isActive;
        $this->_tPriority = $priority;
    }
}