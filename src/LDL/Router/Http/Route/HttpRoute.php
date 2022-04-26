<?php

declare(strict_types=1);

namespace LDL\Router\Http\Route;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;
use LDL\Router\Core\Route\Traits\RouteInterfaceTrait;
use LDL\Router\Http\Collection\HttpMethodCollection;
use LDL\Router\Http\Response\Encoder\HttpResponseEncoderInterface;
use LDL\Router\Http\Response\Encoder\JsonHttpResponseEncoder;
use LDL\Router\Http\Route\Validator\HttpMethodValidator;
use LDL\Validators\Chain\AndValidatorChain;

class HttpRoute implements HttpRouteInterface
{
    use RouteInterfaceTrait;

    /**
     * @var HttpMethodCollection
     */
    private $methods;

    /**
     * @var HttpResponseEncoderInterface
     */
    private $encoder;

    /**
     * @var int
     */
    private $successCode;

    public function __construct(
        string $path,
        iterable $methods,
        iterable $dispatchers,
        string $name,
        string $description = null,
        HttpResponseEncoderInterface $encoder = null,
        int $successCode = ResponseInterface::HTTP_CODE_OK
    ) {
        $this->methods = new HttpMethodCollection($methods);
        $validatorChain = new AndValidatorChain();

        /*
         * The very first validator in every route is which HTTP methods does the route allows
         */
        $validatorChain->getChainItems()->append(new HttpMethodValidator($this->methods));

        $this->_tRouteTraitPath = $path;
        $this->_tRouteTraitName = $name;
        $this->_tRouteTraitDispatchers = new RouteDispatcherCollection($dispatchers);
        $this->_tRouteTraitDescription = $description ?? 'No description';
        $this->_tRouteTraitValidatorChain = $validatorChain;
        $this->encoder = $encoder ?? new JsonHttpResponseEncoder();
        $this->successCode = $successCode;
    }

    public function getSuccessCode(): int
    {
        return $this->successCode;
    }

    public function getMethods(): HttpMethodCollection
    {
        return $this->methods;
    }

    public function getResponseEncoder(): HttpResponseEncoderInterface
    {
        return $this->encoder;
    }
}
