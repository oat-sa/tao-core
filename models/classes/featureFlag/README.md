Feature flag
========

You can now define feature flags in environment global variables or database. 

## How to use it?

You can use feature flag to easily switch off/on parts of TAO functionality.

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

It is import tht we will use `FEATURE_FLAG` prefix for our feature flags to recognise them and they prupose 
in environment variable list. 

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

Ideally changing a existing config should not require a new version of code to be created, backport, deployed, etc. 
We should just switch a feature flag and restart the server (if required).

For historical reasons, TAO has many configurations stored in the filesystem that requires a new version 
of the code/extension to be released/deployed in order to change those values.

The purpose of this feature is to avoid it and do this control based on feature flags.

In order to do it we can use the [FeatureFlagConfigSwitcher](./FeatureFlagConfigSwitcher.php) 
and add by composition [](./FeatureFlagConfigHandlerInterface.php).

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

2) Create a migration to add the new Handler to be used by the switcher.

```php
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;

final class Version202205181448289235_tao extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        /** @var FeatureFlagConfigSwitcher $switcher */
        $switcher = $this->getServiceManager()->getContainer()->get(FeatureFlagConfigSwitcher::class);

        $switcher->addClientConfigHandler(MyClientConfigHandler::class);
        $switcher->addExtensionConfigHandler('taoQtiItem', 'qtiCreator', MyExtensionConfigHandler::class);
    }

    public function down(Schema $schema): void
    {
        /** @var FeatureFlagConfigSwitcher $switcher */
        $switcher = $this->getServiceManager()->getContainer()->get(FeatureFlagConfigSwitcher::class);

        $switcher->removeClientConfigHandler(MyClientConfigHandler::class);
        $switcher->removeExtensionConfigHandler('taoQtiItem', 'qtiCreator', MyExtensionConfigHandler::class);
    }
}
```

3) Create also an installation script

```php
use oat\oatbox\extension\InstallAction;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;

class RegisterServices extends InstallAction
{
    public function __invoke($params)
    {
        /** @var FeatureFlagConfigSwitcher $switcher */
        $switcher = $serviceManager->getContainer()->get(FeatureFlagConfigSwitcher::class);
        $switcher->addClientConfigHandler(MyClientConfigHandler::class);
        $switcher->addExtensionConfigHandler('taoQtiItem', 'qtiCreator', MyExtensionConfigHandler::class);
    }
}
```

4) Load configurations using the `FeatureFlagConfigSwitcher`:

We currently support 2 types of configuration override.

- The ones stored on `client_lib_config_registry.conf.php`, including `featureVisibility`.
- Extension/Module based ones. 

```shell
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;

/** @var FeatureFlagConfigSwitcher $featureFlagConfigSwitcher */
$featureFlagConfigSwitcher; // Get from DI container...

$configs = $featureFlagConfigSwitcher->getSwitchedExtensionConfig('taoQtiItem', 'qtiCreator'); // Config from extension
$configs = $featureFlagConfigSwitcher->getSwitchedClientConfig(); // Config from client_lib_config_registry
```
