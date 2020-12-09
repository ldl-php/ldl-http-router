<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;

use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Validator\HasValidatorChainInterface;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MiddlewareInterface extends IsActiveInterface, LockableObjectInterface, HasValidatorChainInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag $urlParameters
     * @param Router $router
     *
     * @return void
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParameters=null
    ) : void;

    /**
     * @return string
     */
    public function getName() : ?string;

    /**
     * @return array
     */
    public function getResult();

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

}