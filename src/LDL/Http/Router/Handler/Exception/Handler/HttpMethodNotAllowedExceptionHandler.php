<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;

class HttpMethodNotAllowedExceptionHandler extends AbstractExceptionHandler implements ModifiesResponseInterface
{
    private const NAME = 'http.method.disallowed';

    public function __construct(int $priority = self::DEFAULT_PRIORITY, string $name=self::NAME)
    {
        parent::__construct($name, $priority);

        $this->handledExceptions
            ->append(HttpMethodNotAllowedException::class)
            ->lock();
    }

    public function getContent(): ?array
    {
        return null;
    }

    public function handle(\Exception $e) : ?int
    {
        return ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED;
    }
}