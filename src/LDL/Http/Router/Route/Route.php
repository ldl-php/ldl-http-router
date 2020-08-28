<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\FinalDispatcher;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Middleware\MiddlewareInterface;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareCollection;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareInterface;
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
        $this->parseRequestUrlSchema($response, $urlArgs);

        $result = [];

        /**
         * @var MiddlewareInterface $preDispatch
         */
        foreach ($config->getPreDispatchMiddleware()->sort('asc') as $preDispatch) {
            if (false === $preDispatch->isActive()) {
                continue;
            }

            $preResult = $preDispatch->dispatch(
                $this,
                $request,
                $response
            );

            if ($response->getContent()) {
                return;
            }

            $result['pre'][$preDispatch->getNamespace()] = [
                    $preDispatch->getName() => $preResult
            ];
        }

        $main = $config->getDispatcher()->dispatch(
            $request,
            $response,
            $config->getRequestParameters(),
            $config->getUrlParameters()
        );

        $result['main'] = $main;

        $prevResults = [
            'pre' => $result['pre'],
            'main' => $result['main']
        ];

        $final = new PostDispatchMiddlewareCollection();

        /**
         * @var PostDispatchMiddlewareInterface $postDispatch
         */
        foreach ($config->getPostDispatchMiddleware()->sort('asc') as $postDispatch) {
            if (false === $postDispatch->isActive()) {
                continue;
            }

            if($postDispatch instanceof FinalDispatcher){
                $final->append($postDispatch);
                continue;
            }

            $postResult = $postDispatch->dispatch(
                $this,
                $request,
                $response,
                $prevResults
            );

            if ($response->getContent()) {
                return;
            }

            $result['post'][$postDispatch->getNamespace()] = [
                $postDispatch->getName() => $postResult
            ];
        }

        /**
         * @var PostDispatchMiddlewareInterface $finalDispatch
         */
        foreach($final as $finalDispatch){
            $postResult = $finalDispatch->dispatch(
                $this,
                $request,
                $response,
                $result
            );

            $result['post'][$finalDispatch->getNamespace()] = [
                $finalDispatch->getName() => $postResult
            ];
        }

        $parser = $config->getResponseParser();

        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());
        $response->setContent($parser->parse($result));
    }

    // <editor-fold desc="Private methods">
    private function parseRequestUrlSchema(
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

    //</editor-fold>
}