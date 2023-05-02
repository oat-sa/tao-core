# Client Config

## Providing environment variables to Client Config by path

Using the DI container you can set configs by providing config path and it's values using environment variables and ect.

```php
use oat\tao\model\clientConfig\ClientConfigStorage;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class ExampleServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->get(ClientConfigStorage::class)
            ->call(
                'setConfigByPath',
                [
                    [
                        'libConfigs' => [
                            'somePath' => [
                                'someProp' => env('SOME_ENV_VARIABLE')->string(),
                            ],
                        ],
                    ],
                ]
            )
            ->call(
                'setConfigByPath',
                [
                    [
                        'context' => [
                            'somePath' => [
                                'someProp' => env('ANOTHER_ENV_VARIABLE')->int(),
                            ],
                        ],
                    ],
                ]
            );
    }
}
```
