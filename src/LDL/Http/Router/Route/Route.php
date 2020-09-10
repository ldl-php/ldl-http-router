<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\FinalDispatcher;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Middleware\MiddlewareInterface;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareCollection;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareInterface;
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
     */
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : void
    {
        $config = $this->config;

        $result = [];

        $parser = $config->getResponseParser();

        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        try{
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
                    $response,
                    $urlArgs
                );

                if(null !== $preResult){
                    $result['pre'][$preDispatch->getNamespace()] = [
                        $preDispatch->getName() => $preResult
                    ];
                }

                $httpStatusCode = $response->getStatusCode();

                if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                    $response->setContent($parser->parse($result));
                    return;
                }
            }

            $main = $config->getDispatcher()->dispatch(
                $request,
                $response
            );

            $result['main'] = $main;

            $final = new PostDispatchMiddlewareCollection();

            /**
             * @var PostDispatchMiddlewareInterface $postDispatch
             */
            foreach ($config->getPostDispatchMiddleware()->sort('asc') as $postDispatch) {
                if (false === $postDispatch->isActive()) {
                    continue;
                }

                if($postDispatch instanceof FinalDispatcher){
                    if(count($final) > 1){
                        throw new \LogicException('You can only have ONE final post dispatcher');
                    }

                    $final->append($postDispatch);
                    continue;
                }

                $postResult = $postDispatch->dispatch(
                    $this,
                    $request,
                    $response,
                    $result
                );

                if(null !== $postResult){
                    $result['post'][$postDispatch->getNamespace()] = [
                        $postDispatch->getName() => $postResult
                    ];
                }

                $httpStatusCode = $response->getStatusCode();

                if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                    $response->setContent($parser->parse($result));
                    return;
                }

            }

            /**
             * @var PostDispatchMiddlewareInterface $finalDispatch
             */
            foreach($final as $finalDispatch){
                $finalDispatch->dispatch(
                    $this,
                    $request,
                    $response,
                    $result
                );
            }

            $response->setContent($parser->parse($result));

        }catch(\Exception $e){
            $exceptionHandlerCollection = $this->config->getExceptionHandlerCollection();

            if(
                null === $exceptionHandlerCollection ||
                0 === count($exceptionHandlerCollection)
            ){
                return;
            }

            foreach($exceptionHandlerCollection->sort('asc') as $exceptionHandler){
                $httpStatusCode = $exceptionHandler->handle($this->router, $e);

                if(null !== $httpStatusCode){
                    $response->setStatusCode($httpStatusCode);
                    $response->setContent($e->getMessage());
                    break;
                }
            }
        }
    }

}