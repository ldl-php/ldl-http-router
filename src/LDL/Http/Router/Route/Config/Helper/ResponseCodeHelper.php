<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Helper;

class ResponseCodeHelper
{
    public static function generate(
        string $pattern,
        string $fill
    ) : ?array
    {
        if('any' === $pattern){
            return self::parseResponseRange('100-599', $fill);
        }

        if(preg_match('#[0-9]+-[0-9]+#', $pattern)){
            return self::parseResponseRange($pattern, $fill);
        }

        if(preg_match('#\,#', $pattern)){
            return self::parseCommaDelimitedResponseRange($pattern, $fill);
        }

        return null;
    }

    private static function parseCommaDelimitedResponseRange(
        string $responseCodes,
        string $fill
    ) : array
    {
        $codes = array_flip(explode(',', $responseCodes));

        array_walk($codes, static function(&$value, $code) use($fill){
            if($code < 100 || $code > 599){
                $msg = sprintf(
                    'Invalid HTTP response code: "%s"',
                    $code
                );
                throw new \InvalidArgumentException($msg);
            }

            $value = $fill;
        });

        return $codes;
    }

    private static function parseResponseRange(
        string $responseCodes,
        string $fill
    ) : array
    {
        $codes = explode('-', $responseCodes);
        $start = (int) $codes[0];
        $end = (int) $codes[1];

        if($start < 100 || $start > 599){
            $msg = sprintf(
                'Invalid HTTP start response code: "%s"',
                $start
            );
            throw new \InvalidArgumentException($msg);
        }

        if($end < 100 || $end > 599){
            $msg = sprintf(
                'Invalid end response code: "%s"',
                $end
            );
            throw new \InvalidArgumentException($msg);
        }

        if($start >= $end){
            $msg = sprintf('Start response code must be greater than end response code');
            throw new \InvalidArgumentException($msg);
        }

        return array_map(static function() use ($fill){
            return $fill;
        }, array_flip(range($start, $end)));
    }
}