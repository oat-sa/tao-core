# Middlewares usage crash course

### How to create and enable new one:

1. Ensure PSR15 compatibility (implement `\Psr\Http\Server\MiddlewareInterface`)
2. Ensure it's accessible with ServiceManager (extends `ConfigurableService`)
3. To activate please refer `\oat\tao\model\Middleware\MiddlewareManager::append`
4. To deactivate `\oat\tao\model\Middleware\MiddlewareManager::detach`

NB: Currently, middlewares can be attached only to the concrete routes

#### Sample

```injectablephp
$this->getServiceManager()->get(MiddlewareManager::class)
            ->append( new MiddlewareContext(
            [
                MiddlewareContext::PARAM_ROUTE => '/http/uri',
                MiddlewareContext::PARAM_MIDDLEWARE_ID => 'middlewareID'
            ]
        ))->getMiddlewareHandler();
```
