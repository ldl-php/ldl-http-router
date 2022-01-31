<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Response\Response;
use LDL\Router\Http\HttpRouter;

$request = Request::createFromGlobals();

$response = new Response();
$router = new HttpRouter(
    require __DIR__.'/lib/example-http-routes.php'
);

var_dump($router->getRouteList());
