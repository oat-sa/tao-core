# CSRF Token

## Setting different strategies for CSRF Token handling

You can set specific strategy for [\oat\tao\model\security\xsrf\TokenService](./TokenService.php),
by changing its configuration on `config/tao/security-xsrf-token.conf.php`:

```php
<?php
return new oat\tao\model\security\xsrf\TokenService(array(
    'store' => new oat\tao\model\security\xsrf\TokenStoreKeyValue(
        [
            'persistence' => 'redis',
        ]
    ),
    'poolSize' => 10,
    'timeLimit' => 360,
    'validateTokens' => false
));
```

## Using REDIS

In case you are 
using [\oat\tao\model\security\xsrf\TokenStoreKeyValue](./TokenStoreKeyValue.php) with Redis, 
please do not forget to have a CRON job deleting tokens time-to-time to avoid increase your Redis storage.

Check [ClearCsrfTokenTool](./../../../../scripts/tools/Security/ClearCsrfTokenTool.php) for more details.