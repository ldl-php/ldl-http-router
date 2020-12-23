<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;

class ValidationTerminateExceptionHandler extends AbstractExceptionHandler
{
    private const NAME = 'request.validation.terminate.handler';

    public function __construct(int $priority = null, string $name = self::NAME)
    {
        parent::__construct($name, $priority);
    }

    public function handle(\Exception $e): ?int
    {
        return ResponseInterface::HTTP_CODE_BAD_REQUEST;
    }

}