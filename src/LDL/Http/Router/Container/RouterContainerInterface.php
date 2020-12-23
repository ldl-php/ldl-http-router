<?php declare(strict_types=1);

namespace LDL\Http\Router\Container;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepositoryInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Request\Body\Parser\RequestBodyParserRepositoryInterface;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepositoryInterface;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepositoryInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepositoryInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;
use LDL\Type\Collection\Types\Scalar\ScalarCollection;

interface RouterContainerInterface extends HasKeyValidatorChainInterface, HasValueValidatorChainInterface, CollectionInterface
{

    /**
     * @param string $sourceName
     * @return bool
     */
    public function hasSource(string $sourceName) : bool;

    /**
     * @param string $source
     * @param string $parameter
     * @return mixed
     *
     * @throws \Exception
     */
    public function get(string $source, string $parameter=null);

    /**
     * @param string $source
     * @param string $resolver
     * @param string|null $parameter
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getResolved(string $source, string $resolver, string $parameter=null);

    /**
     * @return RequestInterface
     */
    public function getRequest() : RequestInterface;

    /**
     * @return ResponseInterface
     */
    public function getResponse() : ResponseInterface;

    /**
     * @return MiddlewareChainInterface
     */
    public function getDispatchers() : MiddlewareChainInterface;

    /**
     * @return ExceptionHandlerRepositoryInterface
     */
    public function getExceptionHandlers() : ExceptionHandlerRepositoryInterface;

    /**
     * @return RequestBodyParserRepositoryInterface
     */
    public function getRequestBodyParsers() : RequestBodyParserRepositoryInterface;

    /**
     * @return ResponseParserRepositoryInterface
     */
    public function getResponseParsers() : ResponseParserRepositoryInterface;

    /**
     * @return RouteParameterResolverRepositoryInterface
     */
    public function getParameterResolvers() : RouteParameterResolverRepositoryInterface;

    /**
     * @return ResponseFormatterRepositoryInterface
     */
    public function getResponseFormatters() : ResponseFormatterRepositoryInterface;

    /**
     * @return RouteConfigParserRepositoryInterface
     */
    public function getConfigParsers() : RouteConfigParserRepositoryInterface;

    /**
     * @return ScalarCollection
     */
    public function getUrlParameters() : ScalarCollection;

    /**
     * @return MiddlewareChainResultInterface
     */
    public function getResponseResult() : MiddlewareChainResultInterface;

}