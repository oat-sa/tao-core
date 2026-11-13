# AGENTS.md — tao-core (tao)

> Shared pillars (standards, quality / `pr-ready-gate`, Make, commit/PR):
> [nextgen-stack `tao/AGENTS.md`](https://github.com/oat-sa/nextgen-stack/blob/main/tao/AGENTS.md)
> · local: [`../AGENTS.md`](../AGENTS.md).

## 01 — Project Context

**What / why:** `oat-sa/tao-core` (extension id `tao`) is the TAO **back-office platform
core**: auth, ACL, routing, task queue, install/update, client config, and the
shell UI other extensions mount into.

**Not:** Items / Tests / QTI / Media / Delivery / Proctoring. Shared AMD `ui/*`
comes from **`@oat-sa/tao-core-ui`** (pin via `views/package.json`).

**Key directories / stack / constraints:**

```text
manifest.php
actions/                  # legacy PHP modules
controller/               # newer API + middleware
models/classes/           # routing, ACL, taskQueue, …
models/ontology/
scripts/                  # taoUpdate, taoInstall, tools, …
migrations/
views/                    # shell UI, AMD, templates, npm, Grunt
  js/controller/          # + routes.js, backoffice.js
  js/layout/
  js/loader/              # generated bundles
  templates/              # layout.tpl, client_config.tpl, …
test/
```

Entrypoints: `manifest.php`; `client_config.tpl` (`'ui'` → tao-core-ui);
`backoffice.js`; `scripts/taoUpdate.php` (high blast radius).

- Stack: PHP + AMD/Grunt; npm `@oat-sa/tao` under `views/`.
- Versions: `composer.json` / `views/package.json` / CI — never invent pins.
- License: typically **`GPL-2.0-only`** sibling headers (no auto SPDX migration).

**Docs:** [`README.md`](README.md). Shared docs / decision-log rules → parent AGENTS.

## 02 — Standards & Conventions

Package-only below. Family patterns, quality SoT, `pr-ready-gate`, polar-star →
**parent AGENTS**.

**Patterns / structure:**

- Thin `actions/` / controllers; logic in `models/classes/`.
- Treat `client_config.tpl` as load-bearing; keep structures/routes/PHP in sync
  for screens.

**Never do (this package):**

- Fork `ui/*` here; confuse with `generis` or sibling domains.
- Casual `taoUpdate` / installer / migration edits; hand-edit generated loaders.

**Ownership**

| Surface | Own? |
|---------|------|
| Backoffice shell / login / users / settings | **Yes** |
| Shared AMD `ui/*` | **Host / pin** (`@oat-sa/tao-core-ui`) |
| QTI Creator / runners / domain libs | **No** |

## 03 — Build & Test Commands

Shared Make / CI / readiness / commit policy → **parent AGENTS**
([commit/PR policy](https://oat-sa.atlassian.net/wiki/x/_oXmqQ)).

**This package** (from Composer platform root — parent of this directory):

```bash
./vendor/bin/phpunit -c phpunit.xml.dist tao/test/unit
npx grunt eslint:extensionreport --extension=tao --force
npx grunt taobundle --extension=tao
```
