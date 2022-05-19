Feature flag
========

You can now define feature flags in environment global variables or database. 

## How to use it?

You can use feature flag to easily switch off/on parts of TAO functionality.

*IMPORTANT*: It is recommended to use an external cache (i.e `redis`) instead of `phpfile` 
to avoid the need to restart the server after saving a feature flag.  

#### Usage to save a feature flag:

```shell
php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -s FEATURE_FLAG_EXAMPLE -v true
```

#### Usage to get feature flag report:

````shell
php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -r true
````

#### Usage to get info about feature flag
```shell
php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -i FEATURE_FLAG_EXAMPLE
```

#### Clear feature flag cache

Only available for DB feature flags

```shell
php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -cc true
```

### Feature flag naming standard

It is import tht we will use `FEATURE_FLAG_` prefix for our feature flags to recognise them and their purpose. 

### AbstractFeatureFlagFormPropertyMapper

To hide/show form fields you can extend `AbstractFeatureFlagFormPropertyMapper` with your configurable service that has
`OPTION_FEATURE_FLAG_FORM_FIELDS` mapped to list of fields that you want to hide/show. 

```php
<?php
use oat\taoLti\models\classes\LtiProvider\FeatureFlagFormPropertyMapper;    

return new FeatureFlagFormPropertyMapper(
    [
        FeatureFlagFormPropertyMapper::OPTION_FEATURE_FLAG_FORM_FIELDS => [
                'formField_01' => [
                    'FEATURE_FLAG_NAME'
                ]
            ]
    ]
);
```

This configuration will display `formField_01` when `FEATURE_FLAG_NAME` is enabled

### SectionVisibilityFilterInterface

`tao/SectionVisibilityFilter` is responsible for listing sections that can be disabled from user. In order to add more 
sections that have to be disabled/enabled based on feature flag. 

```php
<?php

return new oat\tao\model\menu\SectionVisibilityFilter(array(
    'featureFlagSections' => [
        'sectionName' => [
            'FETURE_FLAG_01'
        ]
    ]
));
``` 

This configuration will display `sectionName` when `FETURE_FLAG_01` is enabled.

### Override configs on execution time

These feature helps to dynamically overrride configs according to a featureFlag and therefore, 
no need for redeployment or server restart.

This is done though the [FeatureFlagConfigSwitcher](./FeatureFlagConfigSwitcher.php) 
and composition with [FeatureFlagConfigHandlerInterface](./FeatureFlagConfigHandlerInterface.php) by following the
steps bellow:

1) Create a handler implementing `FeatureFlagConfigHandlerInterface`.

Example:

```php
declare(strict_types=1);

use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\featureFlag\FeatureFlagConfigHandlerInterface;

class MyClientConfigHandler implements FeatureFlagConfigHandlerInterface
{
    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    public function __invoke(array $configs): array
    {
        if ($this->featureFlagChecker->isEnabled('FEATURE_FLAG_EXAMPLE')) {
            //change the config
        }      
        
        //... apply another config changes if required

        return $configs;
    }
}
```

2) Call the handler in your DI container definition.

```php
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class MyDIContainerServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services->get(FeatureFlagConfigSwitcher::class)
            ->call(
                'addClientConfigHandler',
                [
                    MyClientConfigHandler::class,
                ]
            )->call(
                'addExtensionConfigHandler',
                [
                    'taoQtiItem',
                    'qtiCreator',
                    MyExtensionConfigHandler::class
                ]
            );
    }
}
```

We currently support 2 types of configuration override on `FeatureFlagConfigSwitcher`.

- The ones stored on `client_lib_config_registry.conf.php`, including `featureVisibility`.
- The extension/module based ones. 

```shell
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;

/** @var FeatureFlagConfigSwitcher $featureFlagConfigSwitcher */
$featureFlagConfigSwitcher; // Get from DI container...

$configs = $featureFlagConfigSwitcher->getSwitchedExtensionConfig('taoQtiItem', 'qtiCreator'); // Config from extension
$configs = $featureFlagConfigSwitcher->getSwitchedClientConfig(); // Config from client_lib_config_registry.conf.php
```
