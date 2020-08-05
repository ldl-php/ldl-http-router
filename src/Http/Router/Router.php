<?php

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\Parameter\Exception\ParameterException;
use LDL\Http\Router\Route\RouteInterface;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

class Router
{
    /**
     * @var RouteCollector
     */
    private $collector;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        RouteCollector $collector=null
    )
    {
        $this->collector = $collector ?? new RouteCollector();
        $this->request = $request;
        $this->response = $response;
    }

    public function addRoute(RouteInterface $route) : void
    {
        $request  = $this->request;
        $response = $this->response;

        foreach($route->getMethods() as $m) {
            $this->collector->$m($route->getPrefix(), static function () use ($route, $request, $response) {
                $route->dispatch($request, $response);
            });
        }
    }

    public function addGroup(RouteGroupInterface $group) : void
    {
        $request  = $this->request;
        $response = $this->response;

        $this->collector->group(
            ['prefix' => $group->getName()],
            static function($router) use ($group, $request, $response) {
                /**
                 * @var RouteInterface $r
                 */
                foreach($group as $r){
                    $methods = $r->getMethods();

                    foreach($methods as $m){
                        $router->$m($r->getPrefix(), static function() use ($r, $request, $response){
                            $r->dispatch($request, $response);
                        });
                    }
                }
            }
        );
    }

    public function dispatch() : ResponseInterface
    {
        try {
            $dispatcher = new Dispatcher($this->collector->getData());

            $dispatcher->dispatch(
                $_SERVER['REQUEST_METHOD'],
                parse_url($_SERVER['REQUEST_URI'],
                    \PHP_URL_PATH
                ));
        }catch(ParameterException $e){

        }catch(HttpMethodNotAllowedException $e){

            $this->response->setStatusCode(ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED);

        }


        return $this->response;
    }

}