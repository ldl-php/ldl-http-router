<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultItemInterface;

class ResponseFormatter extends AbstractResponseFormatter
{
    private const NAME = 'ldl.response.formatter.default';

    public function __construct(?string $name=null, ?array $options = null)
    {
        parent::__construct($name ?? self::NAME, $options);
    }

    public function _format(MiddlewareChainResultInterface $result) : ?array
    {
        $data = [];

        /**
         * @var MiddlewareChainResultItemInterface $item
         */
        foreach($result as $item){
            $dispatcher = $item->getDispatcher();
            $dispatcherConfig = $dispatcher->getConfig();
            $dispatcherResult = $item->getResult();

            if(null !== $dispatcherResult && $dispatcherConfig->isPartOfResponse()) {
                $data[] = [$dispatcherConfig->getName() => $dispatcherResult];
            }

        }

        $return = [];

        foreach($data as $item){
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