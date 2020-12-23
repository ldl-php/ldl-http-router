<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Types\Classes\ClassCollection;

interface ExceptionHandlerInterface extends NameableInterface
{
    /**
     * @param string $exceptionClass
     * @return bool
     */
    public function canHandle(string $exceptionClass) : bool;

    /**
     * @return ClassCollection
     */
    public function getHandledExceptions(): ClassCollection;

    /**
     * @param \Exception $e
     *
     * @return int|null
     */
    public function handle(\Exception $e) : ?int;
}