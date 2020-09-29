tao-core
========

[Task Queue Doc](models/classes/taskQueue/README.md)

# Feature flag

You can now define feature flag in `.ENV` global variables. 

## How to use it

You can use feature flag to easly switch off/on parts of TAO functionality.

### AbstractFeatureFlagFormPropertyMapper

To hide/show form fields you can extend `AbstractFeatureFlagFormPropertyMapper` with your configurable service that has
`OPTION_FEATURE_FLAG_FORM_FIELDS` mapped to list of fields that you want to hide/show. 

```
FeatureFlagFormPropertyMapper::OPTION_FEATURE_FLAG_FORM_FIELDS => [
    formField_01 => [
        FEATURE_FLAG_NAME
    ]
]
```

This configuration will display `formField_01` when `FEATURE_FLAG_NAME` is enabled

### SectionVisibilityFilterInterface

`tao/SectionVisibilityFilter` is responsible for listing sections that can be disabled from user. In order to add more 
sections that has to be disabled/enabled based on feature flag. 

```
'featureFlagSections' => [
    'sectionName' => [
        `FETURE_FLAG_01`
    ]
]
``` 

This configuration will display `sectionName` when `FETURE_FLAG_01` is enabled.