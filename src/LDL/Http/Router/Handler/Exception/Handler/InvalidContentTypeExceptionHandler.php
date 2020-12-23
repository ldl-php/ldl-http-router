<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\Exception\InvalidContentTypeException;
use LDL\Http\Router\Router;
use Symfony\Component\HttpFoundation\ParameterBag;

class InvalidContentTypeExceptionHandler extends AbstractExceptionHandler
{
    private const NAME = 'http.content.invalid';

    public function __construct(int $priority = self::DEFAULT_PRIORITY, string $name = self::NAME)
    {
        parent::__construct($name, $priority);

        $this->handledExceptions
            ->append(InvalidContentTypeException::class)
            ->lock();
    }

    public function handle(\Exception $e): ?int
    {
        return ResponseInterface::HTTP_CODE_BAD_REQUEST;
    }
}