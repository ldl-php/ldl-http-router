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
            $result['pre'] = $config->getPreDispatchMiddleware()->dispatch(
                $this,
                $request,
                $response,
                $urlArgs
            );

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                return $result;
            }

            $result['main'] = $config->getDispatcher()->dispatch(
                $request,
                $response
            );

            $result['post'] = $config->getPostDispatchMiddleware()->dispatch(
                $this,
                $request,
                $response,
                $onlyFinal = false
            );

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