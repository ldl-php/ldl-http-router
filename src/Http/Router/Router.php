<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\Parameter\Exception\ParameterException;
use LDL\Http\Router\Route\RouteInterface;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

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

    /**
     * @var CacheAdapterInterface
     */
    private $cacheAdapter;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        CacheAdapterInterface $cacheAdapter=null,
        RouteCollector $collector=null
    )
    {
        $this->collector = $collector ?? new RouteCollector();
        $this->request = $request;
        $this->response = $response;
        $this->cacheAdapter = $cacheAdapter;
    }

    public function addRoute(RouteInterface $route) : void
    {
        $request  = $this->request;
        $response = $this->response;

        $config = $route->getConfig();
        $method = $config->getMethod();

        $this->collector->$method($config->getPrefix(), static function () use ($route, $request, $response) {
            $route->dispatch($request, $response);
        });
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
                    $config = $r->getConfig();
                    $method = $config->getMethod();

                    $router->$method($r->getPrefix(), static function() use ($r, $request, $response){
                        $r->dispatch($request, $response);
                    });
                }
            }
        );
    }

    public function dispatch() : ResponseInterface
    {
        try {
            $dispatcher = new Dispatcher($this->collector->getData());

            $dispatcher->dispatch(
                $this->request->getMethod(),
                $this->request->getRequestUri(),
                \PHP_URL_PATH
            );
        }catch(ParameterException $e){

            $this->response->setContent($e->getMessage());
            $this->response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);

        }catch(HttpMethodNotAllowedException $e){

            $this->response->setContent($e->getMessage());
            $this->response->setStatusCode(ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED);

        }catch(HttpRouteNotFoundException $e){

            $this->response->setContent($e->getMessage());
            $this->response->setStatusCode(ResponseInterface::HTTP_CODE_NOT_FOUND);

        }

        return $this->response;
    }

}