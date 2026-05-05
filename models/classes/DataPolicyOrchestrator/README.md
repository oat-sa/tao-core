# Data Policy Orchestrator handlers

Use this flow when you add support for a new data policy in TAO.

## Mandatory interface

All Data Policy Orchestrator handlers must implement
`oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface`.

Required method signature:

```php
public function handle(DataPolicyMessageInterface $message): void;
```

## Required extensions configuration

`DataPolicyOrchestratorWorker` checks installed extensions before processing.
You can override the required extension ids via environment variable:

```bash
DATA_POLICY_REQUIRED_EXTENSIONS=tao,taoEventLog
```

Notes:

- format is CSV (comma separated extension ids)
- if env is not set, defaults are `tao,taoEventLog`
- if env is set to an empty value, no required-extension warnings are produced

## 1. Create handlers for both stages

For each `policyId`, create two handlers that implement `oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface`:

- a `DataRemoval` handler that removes extension data for `dataSubjectRawId`
- a `FullDataRemovalCheck` handler that verifies no related data remains and throws `DataPolicyException` when data still exists

Keep both handlers idempotent, because Pub/Sub messages can be redelivered.

## 2. Register handlers in DI with the same policy id

Register the handlers in your extension service provider and attach both to proxies with the same `policyId`.

```php
use oat\tao\model\DataPolicyOrchestrator\Handler\DataRemovalHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Handler\FullDataRemovalCheckHandlerProxy;

$services->set(MyDataRemovalHandler::class, MyDataRemovalHandler::class);
$services->set(MyFullDataRemovalCheckHandler::class, MyFullDataRemovalCheckHandler::class);

$services->get(DataRemovalHandlerProxy::class)->call(
    'addHandler',
    ['my-policy-id', service(MyDataRemovalHandler::class)]
);

$services->get(FullDataRemovalCheckHandlerProxy::class)->call(
    'addHandler',
    ['my-policy-id', service(MyFullDataRemovalCheckHandler::class)]
);
```

If you register only one side, the corresponding listener will fail with
`No data policy handlers registered for the selected policy "<policyId>"`.

## 3. Run and verify workers

Workers are executed by script:

```bash
php index.php 'oat\tao\scripts\tools\DataPolicyOrchestrator\DataPolicyOrchestratorWorker' --type removal
php index.php 'oat\tao\scripts\tools\DataPolicyOrchestrator\DataPolicyOrchestratorWorker' --type removal-check
```

Expected behavior:

- `removal`: all registered handlers run, and a confirmation message is always published with collected errors
- `removal-check`: all registered handlers must pass; confirmation is published only when no exception is thrown
