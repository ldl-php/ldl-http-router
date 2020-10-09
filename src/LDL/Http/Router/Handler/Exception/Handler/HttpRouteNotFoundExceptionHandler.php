<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Framework\Base\Traits\IsActiveInterfaceTrait;
use LDL\Framework\Base\Traits\NamespaceInterfaceTrait;
use LDL\Framework\Base\Traits\PriorityInterfaceTrait;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;

class HttpRouteNotFoundExceptionHandler implements ExceptionHandlerInterface
{
    private const NAMESPACE = 'LDLExceptionHandler';
    private const NAME = 'HttpRouteNotFoundExceptionHandler';
    private const DEFAULT_IS_ACTIVE = true;
    private const DEFAULT_PRIORITY = 1;

    use NamespaceInterfaceTrait;
    use PriorityInterfaceTrait;
    use IsActiveInterfaceTrait;

    public function __construct(bool $isActive = null, int $priority = null)
    {
        $this->_tNamespace = self::NAMESPACE;
        $this->_tName = self::NAME;
        $this->_tActive = $isActive ?? self::DEFAULT_IS_ACTIVE;
        $this->_tPriority = $priority ?? self::DEFAULT_PRIORITY;
    }

    public function handle(Router $router, \Exception $e, string $context) : ?int
    {
        return $e instanceof HttpRouteNotFoundException ? ResponseInterface::HTTP_CODE_NOT_FOUND : null;
    }
}