tao-core
========

[Task Queue Doc](models/classes/taskQueue/README.md)

# Feature flag

You can now define feature flag in environment global variables. 

## How to use it

You can use feature flag to easily switch off/on parts of TAO functionality.

### Feature flag name

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

## Advanced Search feature flag
Advanced search feature will be enabled by default (but it requires elastic search library).
You can define `FEATURE_FLAG_ADVANCED_SEARCH_DISABLED=true` or `FEATURE_FLAG_ADVANCED_SEARCH_DISABLED=1` feature flag 
in global environment variables to disable Advanced Search feature.
