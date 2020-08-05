<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\Exception\InvalidParameterException;
use LDL\Http\Router\Route\Parameter\ParameterCollection;
use LDL\Http\Router\Route\Parameter\ParameterInterface;
use Swaggest\JsonSchema\Context;

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
     * @var RouterGuardCollection
     */
    private $guards;

    /**
     * @var ?ParameterCollection
     */
    private $parameters;

    /**
     * Route constructor.
     * @param string $prefix
     * @param array $methods
     * @param RouteDispatcherInterface $dispatcher
     * @param ParameterCollection|null $parameters
     * @param RouterGuardCollection|null $guards
     * @param string $name
     * @param string $description
     * @throws Exception\InvalidMethodException
     */
    public function __construct(
        string $prefix,
        array $methods,
        RouteDispatcherInterface $dispatcher,
        ParameterCollection $parameters=null,
        RouterGuardCollection $guards=null,
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
        $this->guards = $guards;
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

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws InvalidParameterException
     * @throws \Swaggest\JsonSchema\Exception
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response) : void
    {
        $requestParameters = (object)$request->getQuery()->all();

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_BEFORE);

        $schema = $this->parameters->getSchema() ?? $this->parameters->getParametersSchema();

        if(null !== $schema){
            try{
                $context = new Context();
                $context->tolerateStrings = true;
                $schema->in($requestParameters, $context);
            }catch(\Exception $e){
                $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
                throw new InvalidParameterException($e->getMessage());
            }
        }

        $this->dispatcher->dispatch(
            $request,
            $response,
            $this->parameters
        );

        $this->applyGuards($request,$response, RouterGuardInterface::VALIDATE_AFTER);
    }

    private function applyGuards(
        RequestInterface $request,
        ResponseInterface $response,
        string $guardType
    ) : void
    {
        if(null === $this->guards){
            return;
        }

        /**
         * @var RouterGuardInterface $guard
         */
        foreach($this->guards->filterByType($guardType) as $guard){
            $guard->validate($request, $response, $this->parameters);
        }

    }

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards(): ?RouterGuardCollection
    {
        return $this->guards;
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
        $validMethods = ['any','get','post','put','delete', 'head'];

        $diff = array_diff($methods, $validMethods);

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