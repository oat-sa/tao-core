# Browser

Core implementation to deal with browsers and lockdown browsers. 

## Client config

You can extend and override client config by Environment variable `LOCK_DOWN_BROWSER_CONFIG`,
which should contain a JSON with the properties bellow:

| Key                            | Description                                            |
|--------------------------------|--------------------------------------------------------|
| `processCheckInterval`         | Denied-process poll interval (ms); `null` if disabled. |
| `processCheckOnTestNavigation` | Run denied-process checks on item navigation.          |
| `processBlackList`             | Denied process names.                                  |
| `mockActiveProcesses`          | Mock driver: fake running processes for QA.            |
| `simulateBreach`               | QA: enables client-side breach simulation harness.     |
