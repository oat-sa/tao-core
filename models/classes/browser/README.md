# Lock down browser (tao-core)

tao-core provides **client process settings**, **HTTP detection**, and the shared **JavaScript facade** (drivers).

## Client config (`context.lockDownBrowser`)

Process-check settings only (no plugin `active` / `driver` here). Merged by `LockDownBrowserClientConfigurationRetriever` from:

1. `LockDownBrowserClientConfigurationRetriever::DEFAULT_CONFIG`
2. Environment `LOCK_DOWN_BROWSER_CONFIG` (JSON, process fields only)

`ClientConfigStorage` exposes the result on encoded **`context.lockDownBrowser`**.

| Key | Description |
|-----|-------------|
| `processCheckInterval` | Denied-process poll interval (ms); `null` if disabled. |
| `processCheckOnTestNavigation` | Run denied-process checks on item navigation. |
| `processBlackList` | Denied process names. |
| `mockActiveProcesses` | Mock driver: fake running processes for QA. |
| `simulateBreach` | QA: enables client-side breach simulation harness. |

After DI or env changes:

```shell
php index.php 'oat\generis\scripts\tools\ContainerCacheWarmup'
```

## Server detection

`HttpRequestLockDownBrowserDetector` reads `clientKioskApiOk` (query or parsed body) and User-Agent to return `kiosked`, `kioskedMock`, or `null` when no lock down browser session is detected.

## JavaScript (tao extension)

| Asset | Path |
|-------|------|
| Facade, UI protocol, drivers | `tao/views/js/lockDownBrowser/` (AMD `tao/lockDownBrowser/...`) |
| Bundle | `tao/loader/lockDownBrowser.min` — rebuild with `grunt taobundle` from `tao/views/build` |
