<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Parameter\Exception\InvalidParameterException;

use Phroute\Phroute\RouteParser;
use Swaggest\JsonSchema\Context;

class Route implements RouteInterface
{
    /**
     * @var RouteConfig
     */
    private $config;

    public function __construct(RouteConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return RouteConfig
     */
    public function getConfig(): RouteConfig
    {
        return clone($this->config);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $urlArgs
     *
     * @throws InvalidParameterException
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : void
    {
        $config = $this->config;

        $this->parseRequestParameterSchema($request, $response);
        $this->parseRequestHeaderSchema($request, $response);
        $this->parseRequestBodySchema($request, $response);
        $this->parseRequestUrlSchema($request, $response, $urlArgs);

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_BEFORE);

        $cacheManager = $config->getCacheManager();

        if($cacheManager){
            $cacheHit = $cacheManager->has($config->getDispatcher(), $request, $response);

            if($cacheHit){
                return;
            }
        }

        $result = $config->getDispatcher()->dispatch(
            $request,
            $response,
            $config->getRequestParameters(),
            $config->getUrlParameters()
        );

        $response->getHeaderBag()->set('Content-Type', $config->getResponseContentType());

        $isJson = preg_match('#application/json.*#', $config->getResponseContentType());

        if($isJson){
            $result = json_encode($result);
        }

        $response->setContent($result);

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_AFTER);

        if($config->getCacheManager()) {
            $cacheManager->store($config->getDispatcher(), $request, $response);
        }
    }

    // Private methods
    private function parseRequestUrlSchema(
        RequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ) : void
    {
        if(null === $this->config->getUrlParameters()){
            return;
        }

        if(null === $this->config->getUrlParameters()->getSchema()){
            return;
        }

        $parser = new RouteParser();
        $parsed = $parser->parse($this->config->getPrefix());

        $variableParameters = [];

        foreach($parsed[1] as $part){
            if(false === $part['variable']){
                continue;
            }

            $variableParameters[$part['name']] = current($args);
            next($args);
        }

        $schema = $this->config->getUrlParameters()->getSchema();

        try{
            $context = new Context();
            $context->tolerateStrings = true;

            $schema->in(
                (object) $variableParameters,
                $context
            );
        }catch(\Exception $e){
            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
            throw new InvalidParameterException($e->getMessage());
        }

    }

    private function parseRequestHeaderSchema(RequestInterface $request, ResponseInterface $response) : void
    {
        $headerSchema = $this->config->getHeaderSchema();

        if(!$headerSchema) {
            return;
        }

        try{
            $context = new Context();
            $context->tolerateStrings = true;

            $headers = [];
            foreach($request->getHeaderBag()->getIterator() as $name => $value){
                $headers[$name] = is_array($value) ? $value[0] : $value;
            }

            $headerSchema->in(
                (object) $headers,
                $context
            );
        }catch(\Exception $e){
            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
            throw new InvalidParameterException($e->getMessage());
        }
    }

    private function parseRequestBodySchema(RequestInterface $request, ResponseInterface $response) : void
    {
        $bodySchema = $this->config->getBodySchema();

        if(!$bodySchema) {
            return;
        }

        $content = $response->getContent() ? $response->getContent() : '[]';

        try{
            $content = json_decode($content,false,null,\JSON_THROW_ON_ERROR);

            $context = new Context();
            $context->tolerateStrings = true;

            $bodySchema->in(
                $content,
                $context
            );
        }catch(\Exception $e){
            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
            throw new InvalidParameterException($e->getMessage());
        }
    }

    private function parseRequestParameterSchema(RequestInterface $request, ResponseInterface $response) : void
    {
        $requestParameters = (object) $request->getQuery()->all();

        $params = $this->config->getRequestParameters();

        if(null === $params){
            return;
        }

        $schema = $params->getSchema();

        foreach($schema->getProperties()->toArray() as $name => $param){
            $default = null;

            if(null !== $schema) {
                $default = $schema->getProperties()->$name->getDefault();
            }

            if(!isset($requestParameters->$name) && $default){
                $requestParameters->$name = $default;
            }

            $params->get($name)->setValue(isset($requestParameters->$name) ? $requestParameters->$name : null);
        }

        $params->freezeParameters();

        if(null === $schema){
            return;
        }

        try{
            $context = new Context();
            $context->tolerateStrings = true;
            $schema->in($requestParameters, $context);
        }catch(\Exception $e){
            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
            throw new InvalidParameterException($e->getMessage());
        }

    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $guardType
     */
    private function applyGuards(
        RequestInterface $request,
        ResponseInterface $response,
        string $guardType
    ) : void
    {
        $guards = $this->config->getGuards();

        if(null === $guards){
            return;
        }

        /**
         * @var RouterGuardInterface $guard
         */
        foreach($guards->filterByType($guardType) as $guard){
            $guard->validate($request, $response, $this->config->getRequestParameters());
        }

    }

}