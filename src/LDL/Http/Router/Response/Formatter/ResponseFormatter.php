<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Router;

class ResponseFormatter extends AbstractResponseFormatter
{
    public function format(Router $router, MiddlewareChainCollection $collection)
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

        return $return;
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
            $result[][$resultKey] = $routerHandlers->handle(
                    $router,
                    $e,
                    $router->getDispatcher()->getUrlParameters(),
            );

            return;
        }

        try{

            $handlers = $route->getExceptionHandlers();

            $result[][$resultKey] = $handlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

        }catch (\Exception $e){

            $result[][$resultKey] = $routerHandlers->handle(
                $router,
                $e,
                $router->getDispatcher()->getUrlParameters(),
            );

        }
    }

}