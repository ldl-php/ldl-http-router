<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Factory;

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepository;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepositoryInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResult;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Request\Body\Parser\RequestBodyJsonParser;
use LDL\Http\Router\Request\Body\Parser\RequestBodyParserRepository;
use LDL\Http\Router\Request\Body\Parser\RequestBodyParserRepositoryInterface;
use LDL\Http\Router\Response\Formatter\ResponseFormatter;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepository;
use LDL\Http\Router\Response\Formatter\ResponseFormatterRepositoryInterface;
use LDL\Http\Router\Response\Parser\Json\JsonResponseParser;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepository;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepositoryInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepository;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Http\Router\Container\RouterContainer;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Type\Collection\Types\Scalar\ScalarCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class RouterContainerFactory
{

    public static function create(
        iterable $dispatchers,
        RouteParameterResolverRepositoryInterface $parameterResolvers=null,
        RouteConfigParserRepositoryInterface $configParsers=null,
        RequestInterface $request = null,
        ResponseInterface $response = null,
        ScalarCollection $urlParameters = null,
        ExceptionHandlerRepositoryInterface $exceptionHandlers=null,
        RequestBodyParserRepositoryInterface $bodyParsers=null,
        ResponseParserRepository $responseParserRepository=null,
        ResponseFormatterRepositoryInterface $responseFormatterRepository=null,
        MiddlewareChainResultInterface $middlewareChainResult = null
    ) : RouterContainerInterface
    {
        $configParsers  = $configParsers ?? new RouteConfigParserRepository();
        $urlParameters  = $urlParameters ?? new ScalarCollection();
        $exceptionHandlers = $exceptionHandlers ?? new ExceptionHandlerRepository();
        $responseParams = new ParameterBag();

        $responseJsonParser = new JsonResponseParser();
        $requestJsonParser = new RequestBodyJsonParser();

        /**
         * If no response parser repo is passed, create a new instance
         */
        if(null === $responseParserRepository){
            $responseParserRepository = new ResponseParserRepository();
        }

        /**
         * We always need a response parser to reply to a request, so we add the JSON parser
         * and select it, this can of course be changed by the response parser set in the route configuration.
         *
         * But for all other requests which do not have a response parser configuration directive, the JSON parser
         * will be used.
         */
        if(
            null === $responseParserRepository->getSelectedKey() &&
            false === $responseParserRepository->hasKey($responseJsonParser->getName())
        ){
            $responseParserRepository->append($responseJsonParser);
            $responseParserRepository->select($responseJsonParser->getName());
        }

        if(null === $bodyParsers){
            $bodyParsers = new RequestBodyParserRepository();
        }

        if(
            null === $bodyParsers->getSelectedKey() &&
            false === $bodyParsers->hasKey($requestJsonParser->getName())
        ){
            $bodyParsers->append($requestJsonParser);
            $bodyParsers->select($requestJsonParser->getName());
        }

        $defaultResponseFormatter = new ResponseFormatter();

        /**
         * If no response formatter repo is passed, create a new instance
         */
        if(null === $responseFormatterRepository){
            $responseFormatterRepository = new ResponseFormatterRepository();
        }

        /**
         * We always need a response formatter to format a response, so we add the default response formatter
         * and select it, this can of course be changed by the response formatter configuration directive
         * in the each route's configuration.
         *
         * But for all other responses which do not have a response formatter configuration directive, the
         * default response parser will be used.
         */
        if(
            null === $responseFormatterRepository->getSelectedKey() &&
            false === $responseFormatterRepository->hasKey($defaultResponseFormatter->getName())
        ){
            $responseFormatterRepository->append($defaultResponseFormatter);
            $responseFormatterRepository->select($defaultResponseFormatter->getName());
        }

        /**
         * @var RouterContainerInterface $sources
         */
        return new RouterContainer(
            new MiddlewareChain(null, true, $dispatchers),
            $request ?? Request::createFromGlobals(),
            $response ?? new Response(),
            $urlParameters ?? new ScalarCollection(),
            $configParsers ?? new RouteConfigParserRepository(),
            $exceptionHandlers ?? new ExceptionHandlerRepository(),
            $parameterResolvers ?? new RouteParameterResolverRepository(),
            $bodyParsers,
            $responseParserRepository,
            $responseFormatterRepository,
            $middlewareChainResult ?? new MiddlewareChainResult()
        );

    }
}