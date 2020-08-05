<?php

namespace LDL\HTTP\Router\Route;

use LDL\HTTP\Core\Request\RequestInterface;
use LDL\HTTP\Core\Request\ResponseInterface;
use LDL\HTTP\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\HTTP\Router\Route\Parameter\ParameterCollection;

class Route implements RouteInterface
{
    /**
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $methods;

    /**
     * @var ?ParameterCollection
     */
    private $parameters;

    public function __construct(
        string $prefix,
        RouteDispatcherInterface $dispatcher,
        ParameterCollection $parameters=null,
        array $methods = [],
        string $name='',
        string $description=''
    )
    {
        $this->validateMethods($prefix, $methods);

        $this->parameters = $parameters;
        $this->dispatcher = $dispatcher;
        $this->prefix = $prefix;
        $this->name = $name;
        $this->description = $description;
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return RouteDispatcherInterface
     */
    public function getDispatcher(): RouteDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return ParameterCollection|null
     */
    public function getParameters() : ?ParameterCollection
    {
        return $this->parameters;
    }

    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {

        $this->dispatcher->dispatch($request, $response);

    }

    //Private methods

    /**
     * @param string $prefix
     * @param array $methods
     *
     * @throws Exception\InvalidMethodException
     */
    private function validateMethods(
        string $prefix,
        array $methods
    ) : void
    {
        $validMethods = ['any','get','post','put','delete'];

        $diff = array_diff($validMethods, $methods);

        $diffCount = count($diff);

        if(0 === $diffCount){
            return;
        }

        $msg = sprintf(
            'Invalid method%s, specified: "%s" for route with prefix: "%s"',
            $diffCount > 0 ? 's' : '',
            implode(', ', $methods),
            $prefix
        );

        throw new Exception\InvalidMethodException($msg);
    }

}