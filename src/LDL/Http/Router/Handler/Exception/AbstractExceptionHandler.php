<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Framework\Base\Traits\PriorityInterfaceTrait;

abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    use PriorityInterfaceTrait;

    public const DEFAULT_PRIORITY = 1;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, int $priority=self::DEFAULT_PRIORITY)
    {
        $this->name = $name;
        $this->_tPriority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function copy(int $priority): ExceptionHandlerInterface
    {
        $self = clone($this);
        $self->_tPriority = $priority;
        return $self;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
}