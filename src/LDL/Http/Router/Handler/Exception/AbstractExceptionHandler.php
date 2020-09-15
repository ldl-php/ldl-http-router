<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $priority;

    public function __construct(
        string $namespace,
        string $name,
        int $priority,
        bool $isActive
    )
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->active = $isActive;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

}