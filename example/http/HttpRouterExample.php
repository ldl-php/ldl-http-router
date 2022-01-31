<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Http\Exception\HttpRouteNotFoundException;
use LDL\Router\Http\HttpRouter;

$request = Request::createFromGlobals();

$response = new Response();
$router = new HttpRouter(
    require __DIR__.'/../lib/example-http-routes.php'
);

try {
    $result = $router->dispatch($request);
    $response->setStatusCode(ResponseInterface::HTTP_CODE_OK);
    $response->setContent(
        json_encode(
            $result->getArray(),
            \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT
        )
    );
} catch (HttpRouteNotFoundException $e) {
    $response->setStatusCode($e->getCode());
    $response->setContent($e->getMessage());
}

$response->send();
