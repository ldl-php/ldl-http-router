<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Core\Router;

$router = new Router(
    require __DIR__.'/lib/example-routes.php'
);

try {
    $result = $router->match(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '/test/abc');
    foreach ($result->getResult() as $r) {
        dump($r->getDispatcher()->getName());
        dump($r->getDispatcherResult());
    }
} catch (\Exception $e) {
    dump($e->getMessage());
}
