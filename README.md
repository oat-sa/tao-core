# tao-core

[![codecov](https://codecov.io/gh/oat-sa/tao-core/branch/master/graph/badge.svg?token=uPVdj0JrEn)](https://codecov.io/gh/oat-sa/tao-core)

- [Task Queue Doc](models/classes/taskQueue/README.md)
- [Middlewares Doc](models/classes/Middleware/README.md)
- [Feature Flag](models/classes/featureFlag/README.md)
- [CSRF Tokens](models/classes/security/xsrf/README.md)
- [Client Config](models/classes/clientConfig/README.md)

# Webhooks

## Description

Webhooks allow you to send a request to remote server based on triggered event

## How to use it

### Register event webhook.

use command `\oat\tao\scripts\tools\RegisterEventWebhook` to register events that are implementing
`\oat\tao\model\webhooks\configEntity\WebhookInterface` interface.

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

| Variable                                 | Description                                                                                                                          | Default value |
|------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|---------------|
| FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED    | Enable Remote Lists Dependency feature                                                                                               | -             |
| FEATURE_FLAG_ADVANCED_SEARCH_DISABLED    | Disable advanced search feature, if set to 1                                                                                         | -             |
| FEATURE_FLAG_STATISTIC_METADATA_IMPORT   | Enable statistics metadata import                                                                                                    | -             |
| FEATURE_FLAG_CKEDITOR_SOURCEDIALOG       | Enable source editing for ckeditor                                                                                                   | false         |
| FEATURE_FLAG_SOLAR_DESIGN_ENABLED        | Activate the Solar Design mode                                                                                                       | -             |
| GOOGLE_APPLICATION_CREDENTIALS           | Path to GCP credentials path                                                                                                         | -             |
| DATA_STORE_STATISTIC_PUB_SUB_TOPIC       | Topic name for statistic metadata Pub/Sub                                                                                            | -             |
| REDIRECT_AFTER_LOGOUT_URL                | Allows to configure the redirect after logout via environment variable. The fallback is the configured redirect on urlroute.conf.php | -             |
| PORTAL_URL                               | The Portal url used on the back button of Portal theme                                                                               | -             |
| FEATURE_FLAG_TRANSLATION_ENABLED         | Enable access to items/tests translations feature                                                                                    | -             |
| TAO_ALLOWED_TRANSLATION_LOCALES          | Comma separated List of locales available for translations / authoring in the UI. If none provided, all are allowed                  | -             |
| COOKIE_POLICY_CONFIG                     | JSON with CookiePolicy config. Example: `{"privacyPolicyLink":"https://...","cookiePolicyLink":"https://..."}`                         | -             |
| TAO_ID_GENERATOR_MAX_RETRIES             | Maximum number of retry attempts for unique ID generation on collision                                                               | 10            |
| TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS | Enable statement checks during ID generation (set to 1 to enable)                                                                    | 0             |
| TAO_ID_GENERATOR_ID_START                | Starting value for unique ID generation                                                                                               | 1             |
| FEATURE_FLAG_DISPLAY_MAXIMUM_POINTS      | Enable display of maximum points in test authoring                                                                                    | -             |

# Routing

Check more information about actions/controllers and [routing here](./models/classes/routing/README.md)

# Observer implementations

Check the current [observer implementations here](./models/classes/Observer/README.md)
