<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Core\Router;

$router = new Router(
    require __DIR__.'/lib/example-routes.php'
);

try {
    $result = $router->match(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '/frontend-group/abc');
    dump($result->getArray());
} catch (\Exception $e) {
    dump($e->getMessage());
}
