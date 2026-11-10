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
| `displayExitButtonTestRunner`  | Show Exit while a test is running in Kiosked.           |
| `displayExitButtonLogin`       | Show Exit on the login screen in Kiosked.               |
| `displayExitButtonTestSelection` | Show Exit on test selection in Kiosked.               |
| `displayBackToTestSelectionButton` | Show Back to test selection during a Kiosked test.  |
