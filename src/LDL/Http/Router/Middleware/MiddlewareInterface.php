<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;

use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Http\Router\Validator\Request\HasValidatorChainInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MiddlewareInterface extends IsActiveInterface, LockableObjectInterface, HasValidatorChainInterface
{
    /**
     * @return string
     */
    public function getName() : ?string;

    /**
     * @return int|null
     */
    public function getPriority() : ?int;

    /**
     * @param bool $isActive
     * @return MiddlewareInterface
     */
    public function setActive(bool $isActive) : MiddlewareInterface;

    /**
     * @param int $priority
     * @return MiddlewareInterface
     */
    public function setPriority(int $priority) : MiddlewareInterface;

    /**
     * Checks whether the chain has been dispatched or not
     * @return bool
     */
    public function isDispatched() : bool;

    /**
     * Returns the dispatcher configuration
     *
     * @return Config\MiddlewareConfigInterface
     */
    public function getConfig() : Config\MiddlewareConfigInterface;
}