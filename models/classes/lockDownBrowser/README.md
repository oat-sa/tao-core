# Lock down browser (client configuration)

tao-core exposes **process-check** settings to the delivery UI through **client config** (`context.lockDownBrowser`).
The test runner talks to the native lock down browser through a **driver-based JavaScript facade** (real vendor driver vs mock driver for local QA).

**Enable/disable** and **driver** are **not** in client config. They come from the **`lockDownBrowser` test runner plugin** in
`config/taoTests/test_runner_plugin_registry.conf.php` (`active`, `driver`) and are passed to the plugin AMD module as `getConfig()`.

Server-side **detection** (whether an execution used a lock down browser) is separate from client config and lives in
`LockDownBrowserDetectorInterface` (`HttpRequestLockDownBrowserDetector`).

## Client config shape

After login or on delivery pages, decoded `context` includes **process settings only**:

```json
{
  "lockDownBrowser": {
    "processCheckInterval": 60000,
    "processCheckOnTestNavigation": true,
    "processBlackList": [],
    "mockActiveProcesses": [],
    "simulateBreach": false
  }
}
```

| Key | Source | Description |
|-----|--------|-------------|
| `processCheckInterval` | BE defaults / env | Interval in ms for denied-process checks (`null` if disabled). |
| `processCheckOnTestNavigation` | BE defaults / env | Run denied-process checks on item navigation. |
| `processBlackList` | BE defaults / env | Denied process names. |
| `mockActiveProcesses` | BE defaults / env | Mock driver: fake running process names for breach QA. |
| `simulateBreach` | BE defaults / env | QA only: enables breach simulation harness (`taoLockDownBrowserTest.simulateSecurityViolation`). Real Kiosk{ed} uses `checkBlocklist(..., true)` for native block screen; mock shows console + banner. |

Plugin registry (activation + driver):

```php
'taoTestRunnerPlugins/runner/plugins/security/lockDownBrowser' => [
    'id' => 'lockDownBrowser',
    'module' => 'taoTestRunnerPlugins/runner/plugins/security/lockDownBrowser',
    'active' => true,
    'driver' => 'kioskedMock',
    // 'driver' => 'kiosked',
    // ...
],
```

## Process settings (backend)

Merged via `LockDownBrowserClientConfigurationRetriever` from:

1. Defaults — `LockDownBrowserClientConfigurationRetriever::DEFAULT_CONFIG`
2. Environment — `LOCK_DOWN_BROWSER_CLIENT_CONFIG` JSON (process fields only)

On INVALSI, enable/disable and driver are set in **`config/taoTests/test_runner_plugin_registry.conf.php`**. Optional process overrides: env **`LOCK_DOWN_BROWSER_CLIENT_CONFIG`** JSON only.

`ClientConfigStorage` adds retriever output to encoded **`context.lockDownBrowser`**.

After changing DI or env-related config, warm the container cache:

```shell
php index.php 'oat\generis\scripts\tools\ContainerCacheWarmup'
```

## Drivers

| `driver` value (plugin registry) | Use case |
|----------------------------------|----------|
| `kioskedMock` | Development / QA without a real lock down browser. |
| `kiosked` | Production with Kiosk{ed}. |

## Related code

| Area | Location |
|------|----------|
| Client context (process settings) | `LockDownBrowserClientConfigurationRetriever`, `ClientConfigStorage` |
| Plugin activation / driver | `config/taoTests/test_runner_plugin_registry.conf.php`, `DynamicModule` (`driver`) |
| FE facade (service, drivers, UI protocol) | `tao/views/js/lockDownBrowser/` |
| FE test runner plugin | `taoTestRunnerPlugins/views/js/runner/plugins/security/lockDownBrowser.js` |
