<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;

class HttpMethodNotAllowedExceptionHandler implements ExceptionHandlerInterface
{
    private const NAMESPACE = 'LDLExceptionHandler';
    private const NAME = 'HttpMethodNotAllowedExceptionHandler';
    private const DEFAULT_IS_ACTIVE = true;
    private const DEFAULT_PRIORITY = 1;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var int
     */
    private $priority;

    public function __construct(bool $isActive = null, int $priority = null)
    {
        $this->isActive = $isActive ?? self::DEFAULT_IS_ACTIVE;
        $this->priority = $priority ?? self::DEFAULT_PRIORITY;
    }

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function handle(Router $router, \Exception $e): ?int
    {
        return $e instanceof HttpMethodNotAllowedException ? ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED : null;
    }
}