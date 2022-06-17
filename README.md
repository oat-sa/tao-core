tao-core
========

[Task Queue Doc](models/classes/taskQueue/README.md)

[Middlewares Doc](models/classes/Middleware/README.md)

[Feature Flag](models/classes/featureFlag/README.md)

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

# Environment variables

Here you can find the environment variables including feature flags

| Variable                               | Description                                  | Default value |
|----------------------------------------|----------------------------------------------|---------------|
| FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED  | Enable Remote Lists Dependency feature       | -             |
| FEATURE_FLAG_ADVANCED_SEARCH_DISABLED  | Disable advanced search feature, if set to 1 | -             |
| FEATURE_FLAG_STATISTIC_METADATA_IMPORT | Enable statistics metadata import            | -             |
| GOOGLE_APPLICATION_CREDENTIALS         | Path to GCP credentials path                 | -             |
| DATA_STORE_STATISTIC_PUB_SUB_TOPIC     | Topic name for statistic metadata Pub/Sub    | -             |

# Routing

Check more information about actions/controllers and [routing here](./models/classes/routing/README.md)

# Observer implementations

Check the current [observer implementations here](./models/classes/Observer/README.md)