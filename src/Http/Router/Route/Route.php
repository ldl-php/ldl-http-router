<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Parameter\Exception\InvalidParameterException;

use Swaggest\JsonSchema\Context;

class Route implements RouteInterface
{
    /**
     * @var RouteConfig
     */
    private $config;

    public function __construct(RouteConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return RouteConfig
     */
    public function getConfig(): RouteConfig
    {
        return clone($this->config);
    }

    public function dispatch(RequestInterface $request, ResponseInterface $response) : void
    {
        $config = $this->config;

        $requestParameters = (object)$request->getQuery()->all();

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_BEFORE);

        $schema = null;
        $cacheManager = $config->getCacheManager();
        $params = $config->getParameters();

        if($params){
            $schema = $params->getSchema() ?? $params->getParametersSchema();

            try{
                $context = new Context();
                $context->tolerateStrings = true;
                $schema->in($requestParameters, $context);
            }catch(\Exception $e){
                $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
                throw new InvalidParameterException($e->getMessage());
            }
        }

        if($cacheManager){
            $cacheHit = $cacheManager->has($config->getDispatcher(), $request, $response);

            if($cacheHit){
                return;
            }
        }

        $result = $config->getDispatcher()->dispatch(
            $request,
            $response,
            $config->getParameters()
        );

        if(null !== $result){
            $response->setContent(
                $this->config->getContentType() === 'application/json' ? json_encode($result) : $result
            );
        }

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_AFTER);

        if($config->getCacheManager()) {
            $cacheManager->store($config->getDispatcher(), $request, $response);
        }
    }

    // Private methods

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $guardType
     */
    private function applyGuards(
        RequestInterface $request,
        ResponseInterface $response,
        string $guardType
    ) : void
    {
        $guards = $this->config->getGuards();

        if(null === $guards){
            return;
        }

        /**
         * @var RouterGuardInterface $guard
         */
        foreach($guards->filterByType($guardType) as $guard){
            $guard->validate($request, $response, $this->config->getParameters());
        }

    }

}