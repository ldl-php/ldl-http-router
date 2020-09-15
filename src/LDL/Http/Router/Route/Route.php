<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\FinalDispatcher;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Middleware\PreDispatchMiddlewareInterface;
use LDL\Http\Router\Middleware\PostDispatchMiddlewareCollection;
use LDL\Http\Router\Middleware\PostDispatchMiddlewareInterface;
use LDL\Http\Router\Router;

class Route implements RouteInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteConfig
     */
    private $config;

    public function __construct(Router $router, RouteConfig $config)
    {
        $this->router = $router;
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
     * @return array
     * @throws \Exception
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : array
    {
        $config = $this->config;

        $result = [];

        $parser = $config->getResponseParser();

        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        try{
            $preResult = $config->getPreDispatchMiddleware()->dispatch(
                $this,
                $request,
                $response,
                $urlArgs
            );

            if(count($preResult) > 0) {
                $result['pre'] = $preResult;
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                return $result;
            }

            $mainResult = $config->getDispatcher()->dispatch(
                $request,
                $response
            );

            if($mainResult) {
                $result['main'] = $mainResult;
            }

            $postResult = $config->getPostDispatchMiddleware()->dispatch(
                $this,
                $request,
                $response
            );

            if($postResult){
                $result['post'] = $postResult;
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                $response->setContent($parser->parse($result));
                return $result;
            }

            $config->getPostDispatchMiddleware()->dispatchFinal(
                $this,
                $request,
                $response,
                $result
            );

        }catch(\Exception $e){

            /**
             * Handle route specific exceptions, rethrow exception so global exceptions can
             * also be executed.
             */
            $this->config->getExceptionHandlerCollection()->handle($this->router, $e);

            throw $e;

        }

        return $result;
    }

}