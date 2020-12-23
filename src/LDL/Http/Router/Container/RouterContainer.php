<?php declare(strict_types=1);

namespace LDL\Http\Router\Container;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepositoryInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultItemInterface;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Request\Body\Parser\RequestBodyParserRepositoryInterface;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepositoryInterface;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepositoryInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepositoryInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Http\Router\Container\Source\Contract\RouteParameterSourceCallbackInterface;
use LDL\Http\Router\Container\Source\Contract\RouteParameterSourceInterface;
use LDL\Http\Router\Container\Source\Contract\RouteParameterStaticSourceInterface;
use LDL\Http\Router\Container\Source\ContainerSource;
use LDL\Http\Router\Container\Source\ContainerSourceCallback;
use LDL\Http\Router\Container\Source\ContainerStaticSource;
use LDL\Type\Collection\Exception\UndefinedOffsetException;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Types\Scalar\ScalarCollection;
use LDL\Type\Collection\Validator\UniqueValidator;

class RouterContainer extends ObjectCollection implements RouterContainerInterface
{
    use KeyValidatorChainTrait;
    use ValueValidatorChainTrait;

    public const SRC_REPOSITORY = 'sources.all';

    public const SRC_RESPONSE_OBJECT = 'response.object';
    public const SRC_RESPONSE_RESULT_OBJECT = 'response.result';
    public const SRC_RESPONSE_RESULT_ITEM = 'response.result.item';
    public const SRC_RESPONSE_RESULT_PARAM = 'response.result.param';

    public const SRC_REQUEST_URL_PARAM = 'request.url';
    public const SRC_REQUEST_URL_OBJECT = 'request.url.object';
    public const SRC_REQUEST_HEAD_PARAM = 'request.head';
    public const SRC_REQUEST_HEAD_OBJECT = 'request.head.object';
    public const SRC_REQUEST_OBJECT = 'request.object';
    public const SRC_REQUEST_GET_PARAM = 'request.get';
    public const SRC_REQUEST_GET_OBJECT = 'request.get.object';
    public const SRC_REQUEST_POST_PARAM = 'request.post';
    public const SRC_REQUEST_BODY_CONTENT = 'request.body';
    public const SRC_REQUEST_FILES_OBJECT = 'request.files.object';
    public const SRC_REQUEST_FILES_PARAM = 'request.files.param';
    public const SRC_REQUEST_SERVER_OBJECT = 'request.server.object';
    public const SRC_REQUEST_SERVER_PARAM = 'request.server.param';

    public const SRC_CONFIG_PARSER = 'config.parser';
    public const SRC_CONFIG_PARSER_REPOSITORY = 'config.parser.repository';

    public const SRC_ALL_DISPATCHERS = 'dispatchers.all';
    public const SRC_DISPATCHER = 'dispatcher';

    public const SRC_EXCEPTION_HANDLER = 'exception.handler';
    public const SRC_EXCEPTION_HANDLER_REPOSITORY = 'exception.handlers.repository';

    public const SRC_PARAMETER_RESOLVER = 'parameter.resolver';
    public const SRC_PARAMETER_RESOLVER_REPOSITORY = 'parameter.resolver.repository';

    public const SRC_REQUEST_BODY_PARSER = 'request.body.parser';
    public const SRC_REQUEST_BODY_PARSER_REPOSITORY = 'request.body.parser.repository';

    public const SRC_RESPONSE_PARSER = 'response.parser';
    public const SRC_RESPONSE_PARSER_REPOSITORY = 'response.parser.repository';

    public const SRC_RESPONSE_FORMATTER = 'response.formatter';
    public const SRC_RESPONSE_FORMATTER_REPOSITORY = 'response.formatter.repository';

    /**
     * Defined at runtime, these parameters depend highly on what has been configured at a Route level
     */
    public const SRC_REQUEST_BODY_PARSED = 'request.body.parsed';
    public const SRC_REQUEST_ROUTE_CURRENT = 'route.current';

    /**
     * @var MiddlewareChainInterface
     */
    private $dispatchers;

    /**
     * @var ExceptionHandlerRepositoryInterface
     */
    private $exceptionHandlers;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var RouteParameterResolverRepositoryInterface
     */
    private $parameterResolvers;

    /**
     * @var ScalarCollection
     */
    private $urlParameters;

    /**
     * @var RequestBodyParserRepositoryInterface
     */
    private $requestBodyParsers;

    /**
     * @var ResponseParserRepositoryInterface
     */
    private $responseParsers;

    /**
     * @var ResponseFormatterRepositoryInterface
     */
    private $responseFormatters;

    /**
     * @var MiddlewareChainResultInterface
     */
    private $chainResult;

    /**
     * @var RouteConfigParserRepositoryInterface
     */
    private $configParsers;

    public function __construct(
        iterable $dispatchers,
        RequestInterface $request,
        ResponseInterface $response,
        ScalarCollection $urlParameters,
        RouteConfigParserRepositoryInterface $configParsers,
        ExceptionHandlerRepositoryInterface $exceptionHandlers,
        RouteParameterResolverRepositoryInterface $parameterResolvers,
        RequestBodyParserRepositoryInterface $bodyParsers,
        ResponseParserRepository $responseParserRepository,
        ResponseFormatterRepositoryInterface $responseFormatterRepository,
        MiddlewareChainResultInterface $chainResult
    )
    {

        $this->dispatchers = $dispatchers;
        $this->request = $request;
        $this->response = $response;

        $this->exceptionHandlers  = $exceptionHandlers;
        $this->responseParsers    = $responseParserRepository;
        $this->requestBodyParsers = $bodyParsers;
        $this->parameterResolvers = $parameterResolvers;
        $this->responseFormatters = $responseFormatterRepository;
        $this->configParsers      = $configParsers;
        $this->urlParameters      = $urlParameters;
        $this->chainResult        = $chainResult;

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteParameterSourceInterface::class, false))
            ->append(new InterfaceComplianceItemValidator(RouteParameterStaticSourceInterface::class, false))
            ->lock();

        $this->appendMany([
            new ContainerStaticSource(
                self::SRC_ALL_DISPATCHERS,
                $dispatchers
            ),
            new ContainerSource(
                self::SRC_DISPATCHER,
                'offsetGet',
                $dispatchers
            ),
            new ContainerStaticSource(
                self::SRC_REPOSITORY,
                $this
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_OBJECT,
                $request
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_BODY_CONTENT,
                $request->getContent()
            ),
            new ContainerSource(
                self::SRC_REQUEST_GET_PARAM,
                'get',
                $request->getQuery()
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_GET_OBJECT,
                $request->getQuery()
            ),
            new ContainerSource(
                self::SRC_REQUEST_POST_PARAM,
                'get',
                $request
            ),
            new ContainerSource(
                self::SRC_REQUEST_HEAD_PARAM,
                'get',
                $request->getHeaderBag()
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_HEAD_OBJECT,
                $request->getHeaderBag()
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_FILES_OBJECT,
                $request->getFiles()
            ),
            new ContainerSource(
                self::SRC_REQUEST_FILES_PARAM,
                'get',
                $request->getFiles()
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_SERVER_OBJECT,
                $request->getServerParameters()
            ),
            new ContainerSource(
                self::SRC_REQUEST_SERVER_PARAM,
                'get',
                $request->getServerParameters()
            ),
            new ContainerStaticSource(
                self::SRC_RESPONSE_OBJECT,
                $response
            ),
            new ContainerStaticSource(
                self::SRC_EXCEPTION_HANDLER,
                $exceptionHandlers
            ),
            new ContainerSource(
                self::SRC_EXCEPTION_HANDLER_REPOSITORY,
                'offsetGet',
                $exceptionHandlers
            ),
            new ContainerStaticSource(
                self::SRC_PARAMETER_RESOLVER_REPOSITORY,
                $parameterResolvers
            ),
            new ContainerSource(
                self::SRC_PARAMETER_RESOLVER,
                'offsetGet',
                $parameterResolvers
            ),
            new ContainerSource(
                self::SRC_REQUEST_BODY_PARSER,
                'offsetGet',
                $bodyParsers
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_BODY_PARSER_REPOSITORY,
                $bodyParsers
            ),
            new ContainerSource(
                self::SRC_RESPONSE_PARSER,
                'offsetGet',
                $responseParserRepository
            ),
            new ContainerStaticSource(
                self::SRC_RESPONSE_PARSER_REPOSITORY,
                $responseParserRepository
            ),
            new ContainerSource(
                self::SRC_RESPONSE_FORMATTER,
                'offsetGet',
                $responseFormatterRepository
            ),
            new ContainerStaticSource(
                self::SRC_RESPONSE_FORMATTER_REPOSITORY,
                $responseFormatterRepository
            ),
            new ContainerSource(
                self::SRC_CONFIG_PARSER,
                'offsetGet',
                $configParsers
            ),
            new ContainerStaticSource(
                self::SRC_CONFIG_PARSER_REPOSITORY,
                $configParsers
            ),
            new ContainerSource(
                self::SRC_REQUEST_URL_PARAM,
                'offsetGet',
                $urlParameters
            ),
            new ContainerStaticSource(
                self::SRC_REQUEST_URL_OBJECT,
                $urlParameters
            ),
            new ContainerSource(
                self::SRC_RESPONSE_RESULT_ITEM,
                'offsetGet',
                $chainResult
            ),
            new ContainerStaticSource(
                self::SRC_RESPONSE_RESULT_OBJECT,
                $chainResult
            ),
            new ContainerSourceCallback(
                self::SRC_RESPONSE_RESULT_PARAM,
                'offsetGet',
                $chainResult,
                static function(MiddlewareChainResultItemInterface $item){
                    return $item->getResult();
                }
            )
        ]);
    }

    public function getRequest() : RequestInterface
    {
        return $this->request;
    }

    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param RouteParameterSourceInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function getSource(string $sourceName)
    {
        try{

            return $this->offsetGet($sourceName);

        }catch(UndefinedOffsetException $e){

            $msg = "No parameter source named \"$sourceName\" was found";
            throw new Exception\UndefinedContainerSourceException($msg);
        }

    }

    public function get(string $source, string $parameter=null)
    {
        /**
         * @var RouteParameterSourceInterface|RouteParameterStaticSourceInterface $source
         */
        try {
            $source = $this->offsetGet($source);
        }catch(UndefinedOffsetException $e){
            $msg = "Undefined parameter source: \"$source\"";
            throw new Exception\UndefinedContainerSourceException($msg);
        }

        if($source instanceof RouteParameterStaticSourceInterface){
            return $source->getValue();
        }

        $method = $source->getMethod();
        $value = $source->getObject()->$method($parameter);

        if($source instanceof RouteParameterSourceCallbackInterface){
            return $source->getCallback()($value);
        }

        return $value;
    }

    public function getResolved(string $source, string $resolver, string $parameter=null)
    {
        /**
         * @var RouteParameterResolverInterface $resolver
         */
        $resolver = $this->parameterResolvers->offsetGet($resolver);

        return $resolver->resolve($this->get($source, $parameter));
    }

    public function hasSource(string $sourceName) : bool
    {
        try {
            $this->offsetGet($sourceName);
            return true;
        }catch(UndefinedOffsetException $e){
            return false;
        }
    }

    public function getExceptionHandlers() : ExceptionHandlerRepositoryInterface
    {
        return $this->exceptionHandlers;
    }

    public function getRequestBodyParsers() : RequestBodyParserRepositoryInterface
    {
        return $this->requestBodyParsers;
    }

    public function getResponseParsers() : ResponseParserRepositoryInterface
    {
        return $this->responseParsers;
    }

    public function getDispatchers() : MiddlewareChainInterface
    {
        return $this->dispatchers;
    }

    public function getParameterResolvers() : RouteParameterResolverRepositoryInterface
    {
        return $this->parameterResolvers;
    }

    public function getResponseFormatters() : ResponseFormatterRepositoryInterface
    {
        return $this->responseFormatters;
    }

    public function getConfigParsers() : RouteConfigParserRepositoryInterface
    {
        return $this->configParsers;
    }

    public function getUrlParameters() : ScalarCollection
    {
        return $this->urlParameters;
    }

    public function getResponseResult() : MiddlewareChainResultInterface
    {
        return $this->chainResult;
    }

}