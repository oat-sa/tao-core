# Routing

## Actions/Controllers

Thew new action controllers MUST implement [ActionInterface](./Contract/ActionInterface.php).

This is required as a step to decoupling our controllers from abstractions and moving towards
the usage of _Middlewares_ and _DI Container_.

**IMPORTANT:** Please, DO NOT create new controllers extending previous legacy controllers, like [Controller](../http/Controller.php).  

```php
use oat\tao\model\routing\Contract\ActionInterface;
use Psr\Http\Message\ResponseInterface;

class MyAction implements ActionInterface
{
    public function foo(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Hello world');
        
        return $response;
    }
}
```

## Actions/Controllers as part of DI container

How it works:

- You can optionally add the `actions/controllers` as `DI container services`.
- If you do not do that, the constructor or methods parameters will be **autowired**.

### Option 1 - Services as constructor parameters

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyActionController
{
    /** @var ServerRequestInterface */
    private $request;
    
    /** @var ServerRequestInterface */
    private $response;

    public function __construct(ServerRequestInterface $request, ResponseInterface $response) 
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function foo(): ResponseInterface
    {
        $bar = $this->request->getQueryParams()['foo'];
        $this->response->getBody()->write('Hello ' . $bar);
        
        return $this->response;
    }
}
```

### Option 2 - Services as method parameters

**Not recommended**, because you can have multiple implementations of the same service in your container.

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyActionController
{
    public function foo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $bar = $request->getQueryParams()['foo'];
        $response->getBody()->write('Hello ' . $bar);
        
        return $response;
    }
}
```

## Accessing the container inside a Legacy Controller

**IMPORTANT**: Please, DO NOT create legacy controllers anymore, these instructions
are only useful in case you need to maintain and LegacyController.

For this, you just need to use this method `$this->getPsrContainer()`.

```php
use oat\tao\model\http\Controller;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MyController extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function myMethod(): void
    {
        $service = $this->getPsrContainer()->get(MyService::class);
        // Other logic...
    }
}
```
