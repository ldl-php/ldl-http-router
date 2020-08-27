<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Exception\InvalidContentTypeException;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Route\Parameter\Exception\ParameterException;
use LDL\Http\Router\Route\RouteInterface;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;

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

    /**
     * @param RouteInterface $route
     * @param RouteGroupInterface|null $group
     * @return Router
     *
     * @throws Exception\InvalidHttpMethodException
     */
    public function addRoute(RouteInterface $route, RouteGroupInterface $group=null) : self
    {
        $request  = $this->request;
        $response = $this->response;

        $config = $route->getConfig();
        $method = $config->getRequestMethod();

        if(!method_exists($this->collector, $method)){
            $msg = sprintf(
                '%s is not a recognized method',
                $method
            );

            $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);

            throw new Exception\InvalidHttpMethodException($msg);
        }

        $path = "v{$config->getVersion()}/{$config->getPrefix()}";

        if(null !== $group){
            $path = "{$group->getPrefix()}/$path";
        }

        $this->collector->$method($path, static function () use ($route, $request, $response, $path) {
            $route->dispatch($request, $response, func_get_args());
        });

        return $this;
    }

    public function addGroup(RouteGroupInterface $group) : self
    {
        foreach($group->getRoutes() as $r){
            $this->addRoute($r, $group);
        }

        return $this;
    }

    public function dispatch() : ResponseInterface
    {
        try {
            $dispatcher = new Dispatcher($this->collector->getData());

            $dispatcher->dispatch(
                $this->request->getMethod(),
                parse_url($this->request->getRequestUri(), \PHP_URL_PATH)
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

        }catch(InvalidContentTypeException $e){

            $this->response->setContent($e->getMessage());
            $this->response->setStatusCode(ResponseInterface::HTTP_CODE_METHOD_NOT_ALLOWED);

        }

        return $this->response;
    }

}