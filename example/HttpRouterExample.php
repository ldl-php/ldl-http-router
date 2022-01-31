<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Dispatcher\Collection\Result\RouteDispatcherCollectionResult;
use LDL\Router\Http\Exception\HttpRouteNotFoundException;
use LDL\Router\Http\HttpRouter;

$request = Request::createFromGlobals();

$response = new Response();
$router = new HttpRouter(
    require __DIR__.'/lib/example-http-routes.php'
);

try {
    $result = $router->dispatch($request);

    $return = [];

    /**
     * @var RouteDispatcherCollectionResult $r
     */
    foreach ($result as $r) {
        $return[] = [$r->getDispatcher()->getName() => $r->getDispatcherResult()];
    }
    $response->setStatusCode(ResponseInterface::HTTP_CODE_OK);
    $response->setContent(
        json_encode(
            $return,
            \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT
        )
    );
} catch (HttpRouteNotFoundException $e) {
    $response->setStatusCode($e->getCode());
    $response->setContent($e->getMessage());
}

$response->send();
