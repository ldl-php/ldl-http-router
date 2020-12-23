<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Handler;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

class HttpRouteNotFoundExceptionHandler extends AbstractExceptionHandler implements ModifiesResponseInterface
{
    private const NAME = 'http.route.not_found';

    public function __construct(int $priority = self::DEFAULT_PRIORITY, string $name=self::NAME)
    {
        parent::__construct($name, $priority);

        $this->handledExceptions
            ->append(HttpRouteNotFoundException::class)
            ->lock();
    }

    public function getContent(): ?array
    {
        return null;
    }

    public function handle(\Exception $e) : ?int
    {
        return ResponseInterface::HTTP_CODE_NOT_FOUND;
    }
}