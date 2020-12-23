<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Validator\Request\AbstractRequestValidator;
use LDL\Http\Router\Validator\Request\Exception\RequestValidateException;
use LDL\Http\Router\Validator\Response\AbstractResponseValidator;
use LDL\Http\Router\Validator\Response\Exception\ResponseValidateException;
use LDL\Http\Router\Validator\Response\ResponseValidatorInterface;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepository;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepository;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverInterface;
use LDL\Http\Router\Middleware\MiddlewareTrait;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Router\Middleware\MiddlewareInterface;
use LDL\Http\Router\Container\Factory\RouterContainerFactory;

class Dispatcher implements MiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @param string $name
     * @param RequestInterface $request
     * @return array
     */
    public function dispatch(string $name, RequestInterface $request)
    {
        return [
            'result' => $name
        ];
    }
}

class Dispatcher2 implements MiddlewareInterface
{
    use MiddlewareTrait;

    public function dispatch()
    {
        throw new \InvalidArgumentException('test');
    }
}

class Dispatcher3 implements MiddlewareInterface
{
    use MiddlewareTrait;

    public function dispatch(UserCollection $users, array $dispatcherResult) : array
    {
        return [
            'result' => serialize($users),
            'first' => $dispatcherResult
        ];
    }
}

class TestExceptionHandler extends AbstractExceptionHandler
{
    public function handle(\Exception $e): ?int
    {
        return ResponseInterface::HTTP_CODE_FORBIDDEN;
    }
}

/**
 * Class ConfigParser
 *
 * Useful for plugin developers to implement a custom route configuration
 */
class ConfigParser implements RouteConfigParserInterface
{
    public function parse(RouteInterface $route): void
    {
        $rawConfig = $route->getConfig()->getRawConfig();

        if(!array_key_exists('customConfig', $rawConfig)){
            return;
        }

    }
}

$response = new Response();

class AgeRequestValidator extends AbstractRequestValidator
{
    private const NAME = 'age.request.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getParameterSources()->getRequest();

        if((int) $request->get('age') < 50){
            throw new RequestValidateException("Age must be greater than 50", Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

class NameLengthValidator extends AbstractRequestValidator
{
    private const NAME = 'name.length.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getParameterSources()->getRequest();

        if(strlen($request->get('name')) < 3){
            throw new RequestValidateException('Name must be larger than 3 characters', Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

class ContentTypeValidator extends AbstractRequestValidator
{
    private const NAME = 'content-type.validator';

    public function __construct(
        ?string $name=null,
        bool $isStrict = true
    )
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router): ?array
    {
        $request = $router->getRequest();

        if($request->getHeaderBag()->get('Content-Type') !== 'application/json'){
            throw new RequestValidateException("Invalid content type", Response::HTTP_CODE_BAD_REQUEST);
        }

        return null;
    }
}

class NameResolver implements RouteParameterResolverInterface
{
    private const NAME = 'name.resolver';

    use NameableTrait;

    public function __construct(string $name = null)
    {
        $this->_tName = $name ?? self::NAME;
    }

    public function resolve($value) : string
    {
        return "Your name is: $value";
    }

}

class User{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}

class UserCollection extends \LDL\Type\Collection\Types\Object\ObjectCollection
{
    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new \LDL\Type\Collection\Types\Object\Validator\ClassComplianceItemValidator(User::class))
            ->lock();
    }

}

class UsersResolver implements RouteParameterResolverInterface
{
    private const NAME = 'users.resolver';

    use NameableTrait;

    public function __construct(string $name = null)
    {
        $this->_tName = $name ?? self::NAME;
    }

    public function resolve($users) : UserCollection
    {
        $collection = new UserCollection();

        foreach($users as $user){
            $collection->append(new User((string) $user['id']));
        }

        return $collection;
    }

}

/**
<service id="blah" class="MiddlewareChain">
<argument type="string">nombre_del_chain</argument>
<argument type="collection">
    <argument type="service" id="chain" />
     bv
    <argument type="service" id="chain2" />
</argument>
<tag name="middleware.chain">
</service>


class MyChain extends \LDL\Http\Router\Middleware\MiddlewareChain
{

}
*/

class AgeResolver implements RouteParameterResolverInterface
{
    private const NAME = 'age.resolver';

    use NameableTrait;

    public function __construct(string $name = null)
    {
        $this->_tName = $name ?? self::NAME;
    }

    public function resolve($value) : string
    {
        return "Years old: $value";
    }
}

class AgeResponseValidator extends AbstractResponseValidator
{
    private const NAME = 'age.response.validator';

    public function __construct(?string $name=null, bool $isStrict = true)
    {
        parent::__construct($name ?? self::NAME, $isStrict);
    }

    public function _validate(Router $router, array $result = null): ?array
    {
        if(null === $result){
            return null;
        }

        if((int) $result['globalPre']['RouterPre']['age'] >= 60){
            throw new ResponseValidateException("Age must be lower than 60", ResponseValidatorInterface::HTTP_BAD_RESPONSE);
        }

        return null;
    }
}

$allResolvers = (new RouteParameterResolverRepository())
    ->append(new AgeResolver())
    ->append(new NameResolver())
    ->append(new UsersResolver());

$configParserRepository = (new RouteConfigParserRepository())
->append(new ConfigParser());

$router = new Router(
    RouterContainerFactory::create(
        [
            new Dispatcher('dispatcher'),
            new Dispatcher2('dispatcher2'),
            new Dispatcher3('dispatcher3'),
        ],
        $allResolvers,
        $configParserRepository
    )
);

$router->getRequestValidatorChain()
    ->append(new ContentTypeValidator())
    ->append(new AgeRequestValidator())
    ->append(new NameLengthValidator());

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router
);

$group = new RouteGroup('Test Group', 'test', $routes);

$router->addGroup($group);

/** Event subscription example

$router->getEventBus()->subscribeTo('route.main.dispatcher.before', static function($result){
    dd($result);
});

$router->getEventBus()->subscribeTo('route.main.dispatcher.after', static function($result){
    dd($result);
});
*/


$router->dispatch()->send();
