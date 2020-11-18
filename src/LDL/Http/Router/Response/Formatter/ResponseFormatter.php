<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Router;

class ResponseFormatter extends AbstractResponseFormatter
{
    public function _format(Router $router, MiddlewareChainCollection $collection) : ?array
    {
        $result = [];

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($collection as $chain){

            $hasException = $chain->getLastException();

            $chainResult = $chain->getResult();

            if(count($chainResult) > 0) {
                $result[] = $chainResult;
            }

            if($hasException){
                $this->parseException($router, $chain, $hasException, $result);
                continue;
            }

        }

        $return = [];

        foreach($result as $item){
            foreach($item as $key => $value) {
                if(array_key_exists($key, $return)){
                    $return[$key] = [
                        $return[$key],
                        $value
                    ];
                    continue;
                }
                $return[$key] = $value;
            }
        }

        return count($return) > 0 ? $return : null;
    }

    private function parseException(
        Router $router,
        MiddlewareChainInterface $chain,
        \Exception $e,
        array &$result
    ) : void
    {
        $lastExecutedDispatcher = $chain->getLastExecutedDispatcher();
        $resultKey = $lastExecutedDispatcher->getName();
        $route = $router->getCurrentRoute();
        $routerHandlers = $router->getExceptionHandlerCollection();

        if(null === $route){
            $exception = $routerHandlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception) {
                $result[][$resultKey] = $exception;
            }

            return;
        }

        try{

            $handlers = $route->getExceptionHandlers();

            $exception = $handlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception) {
                $result[][$resultKey] = $exception;
            }

        }catch (\Exception $e){

            $exception = $routerHandlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

            if(null !== $exception){
                $result[][$resultKey] = $exception;
            }

        }
    }

}