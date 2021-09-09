tao-core
========

[Task Queue Doc](models/classes/taskQueue/README.md)

[Middlewares Doc](models/classes/Middleware/README.md)

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


# Webhooks

## Description

Webhooks allow you to send a request to remote server based on triggered event

## How to use it

### Register event webhook.

use command `\oat\tao\scripts\tools\RegisterEventWebhook` to register events that are implementing `\oat\tao\model\webhooks\configEntity\WebhookInterface` interface.

i.e:
```
$ php index.php 'oat\tao\scripts\tools\RegisterEventWebhook' 
    \ -u "https://example.com"
    \ -m "POST"
    \ -e "<<Class FQN>>"
``` 

# Check ACL Permissions

In order to check ACL permissions, you can use the `PermissionChecker`:

```php

$permissionChecker = $this->getServiceLocator()->get(oat\tao\model\accessControl\PermissionChecker::class);

$permissionChecker->hasWriteAccess('resourceId');
$permissionChecker->hasReadAccess('resourceId');
$permissionChecker->hasGrantAccess('resourceId');
```

**Important**: It takes into consideration the current user in the session, if no user is provided.

# Roles Access (rules and action permissions)
## Description
Script allow you to apply (add)/revoke (remove) list of rules and/or permissions to a specific roles and actions.

## How to use it
Execute the following command to apply (add) new rules/permissions:
```
$ php index.php 'oat\tao\scripts\tools\accessControl\SetRolesAccess' \
--config [config.json|json_string]
```
If you want to revoke (remove) them, add `--revoke` flag:
```
$ php index.php 'oat\tao\scripts\tools\accessControl\SetRolesAccess' \
--revoke \
--config [config.json|json_string]
```

### Config example
```json
{
    "rules": {
        "role": [
            {
                "ext": "extensionIdentifier",
                "mod": "actionControllerName",
                "act": "actionMethodName"
            }
        ]
    },
    "permissions": {
        "controller": {
            "action": {
                "rule1": "READ",
                "rule2": "WRITE"
            }
        }
    }
}
```

## Lists Dependency feature flag
Lists Dependency feature will be disabled by default.
You can define `FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED=1` feature flag in global environment variables to enable  
Lists Dependency feature. 
