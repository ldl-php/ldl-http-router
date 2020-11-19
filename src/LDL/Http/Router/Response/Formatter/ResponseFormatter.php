<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;

class ResponseFormatter extends AbstractResponseFormatter
{
    public function _format(MiddlewareChainCollection $collections) : ?array
    {
        $result = [];

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($collections as $chain){

            $chainResult = $chain->getResult();

            if(null !== $chainResult) {
                $chainName = $chain->getName();
                $result[] = null === $chainName ? $chainResult : [$chainName => $chainResult];
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
}