<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Repository;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Handler\Exception\Handler\HttpMethodNotAllowedExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\HttpRouteNotFoundExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\InvalidContentTypeExceptionHandler;
use LDL\Http\Router\Validator\Exception\Handler\ValidationTerminateExceptionHandler;

class ExceptionHandlerRepositoryFactory
{

    public static function create(
        array $handlers=[],
        bool $select=true
    ) : ExceptionHandlerRepositoryInterface
    {
        $repo = new ExceptionHandlerRepository(
            array_merge(
                [
                    new HttpRouteNotFoundExceptionHandler(),
                    new InvalidContentTypeExceptionHandler(),
                    new HttpMethodNotAllowedExceptionHandler(),
                    new ValidationTerminateExceptionHandler()
                ],
                $handlers
            )
        );

        if(false === $select){
            return $repo;
        }

        foreach($repo as $item){
            $repo->select($item->getName());
        }

        return $repo;
    }
}