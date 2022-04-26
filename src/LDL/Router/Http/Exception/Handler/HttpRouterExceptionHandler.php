<?php

declare(strict_types=1);

namespace LDL\Router\Http\Exception\Handler;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Http\HttpRouterInterface;
use LDL\Router\Http\Response\Exception\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class HttpRouterExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var bool
     */
    private $throwAlways;

    /**
     * @var bool
     */
    private $throwOnUnknown;

    /**
     * @var bool
     */
    private $hideResponseMessage;

    /**
     * @var string
     */
    private $hideUnknownMessage;

    public function __construct(
        bool $throwAlways = false,
        bool $throwOnUnknown = false,
        bool $hideResponseMessage = false,
        bool $hideUnknownMessage = true
    ) {
        $this->throwAlways = $throwAlways;
        $this->throwOnUnknown = $throwOnUnknown;
        $this->hideResponseMessage = $hideResponseMessage;
        $this->hideUnknownMessage = $hideUnknownMessage;
    }

    public function handle(
        \Throwable $e,
        HttpRouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response
    ): void {
        /*
         * Throw always, even if the exceptions are response exceptions
         */
        if ($this->throwAlways) {
            throw $e;
        }

        if ($e instanceof HttpResponseException) {
            $code = (int) $e->getCode();
            $response->setStatusCode($code >= 100 && $code <= 599 ? $code : ResponseInterface::HTTP_CODE_BAD_RESPONSE);
            $response->setContent($this->hideResponseMessage ? 'An error has ocurred' : $e->getMessage());

            return;
        }

        if ($this->throwOnUnknown) {
            throw $e;
        }

        $response->setStatusCode(ResponseInterface::HTTP_CODE_INTERNAL_SERVER_ERROR);
        $response->setContent($this->hideUnknownMessage ? 'Internal server error' : $e->getMessage());
    }
}
