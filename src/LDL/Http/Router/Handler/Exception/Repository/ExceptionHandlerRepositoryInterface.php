<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Repository;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\MultipleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;

interface ExceptionHandlerRepositoryInterface extends CollectionInterface, HasKeyValidatorChainInterface, MultipleSelectionInterface
{
    /**
     * @param \Exception $exception
     * @return ExceptionHandlerRepositoryInterface
     */
    public function handle(\Exception $exception) : ExceptionHandlerRepositoryInterface;

    /**
     * Return the last executed exception handler
     *
     * @return ExceptionHandlerInterface|null
     */
    public function getLastExecutedExceptionHandler() : ?ExceptionHandlerInterface;

    /**
     * Tells the user if it can handle an exception of type $class
     * if strict is set to true, the handlers in the repository will not use instanceof for comparison
     *
     * @param string $class
     * @param bool $strict
     *
     * @return bool
     */
    public function canHandle(string $class, bool $strict = false) : bool;


    /**
     * Returns a response code, if self::handle has not been called, it must throw an exception
     *
     * @throws \Exception
     *
     * @return int
     */
    public function getResponseCode() : int;
}
