<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Framework\Base\Traits\PriorityInterfaceTrait;
use LDL\Type\Collection\Types\Classes\ClassCollection;

abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    use PriorityInterfaceTrait;
    use NameableTrait;

    public const DEFAULT_PRIORITY = 1;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ClassCollection
     */
    protected $handledExceptions;

    public function __construct(
        string $name,
        ?int $priority=null
    )
    {
        $this->_tName = $name;
        $this->_tPriority = $priority ?? self::DEFAULT_PRIORITY;
        $this->handledExceptions = new ClassCollection();
    }

    public function getHandledExceptions(): ClassCollection
    {
        return $this->handledExceptions;
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

    public function canHandle(string $exceptionClass): bool
    {
        return $this->getHandledExceptions()->hasValue($exceptionClass);
    }
}