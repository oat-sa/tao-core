# AGENTS.md — tao-core

## Purpose

`oat-sa/tao-core` is the TAO **back-office platform core** (extension id `tao`): auth, ACL, routing, task queue, install/update, client config, and the shell UI that other extensions mount into.

It is **not** the domain home for Items, Tests, QTI, Media, Delivery, or Proctoring — those live in sibling extensions. Shared UI widgets consumed via AMD alias `ui` are provided by **`@oat-sa/tao-core-ui`**, which this package hosts through `views/package.json` — do not fork that UI into domain extensions.

## Stack

Do **not** hardcode dependency or runtime versions in this file. Read current pins from:

| What | Source of truth |
|------|-----------------|
| Package license | `composer.json` → `license` (also root `LICENSE`) |
| PHP / oat-sa Composer deps | `composer.json` → `require` / `require-dev` |
| PHP versions gated in CI | `.github/workflows/continuous-integration.yaml` (matrix) |
| FE npm deps (`@oat-sa/tao-core-*`, jquery, …) | `views/package.json` |
| FE toolchain scripts | `views/build/package.json` |

Shape of the stack (stable facts, not version pins):

- **PHP** platform package on top of `oat-sa/generis` (RDF / oatbox)
- Other oat-sa Composer libs as declared in `composer.json` (e.g. installer, jig, LTI, npm-bridge)
- **FE** — RequireJS AMD + Grunt bundles; npm package `@oat-sa/tao` under `views/`
- **Not used here** — Vue / React / Svelte / Webpack app stacks

## Core Rules

- **Follow existing patterns first.** Nearest similar `actions/` / `controller/` / `models/classes/` / AMD module before inventing a new approach.
- **Prefer TDD.** For new behavior or bugfixes: write or extend a failing test first, then implement until it passes; avoid “code first, tests later” unless the change is docs/config-only.
- **Prefer minimal, local changes.** No broad refactors unless explicitly requested.
- **Preserve license headers** on touched files. Extend copyright year ranges to include the current calendar year; new files use the current year only. Match the nearest sibling file header (classic GPL block as used in this tree). Do **not** auto-migrate to SPDX dual-license unless project policy for this repo explicitly changes — today `composer.json` declares **`GPL-2.0-only`**.
- **Update tests** when behavior changes (`test/unit`, `test/integration`).
- **Do not weaken** lint / format / CI / test gates to make a change pass; fix the change.
- **Verify before done:** satisfy the Readiness gate below and report real command results.

## Structure

```text
manifest.php              # extension bootstrap: routes, ACL, install, providers
actions/                  # legacy PHP modules (Main, Users, Search, …)
controller/               # newer API + middleware
models/classes/           # domain services (routing, ACL, taskQueue, …)
models/ontology/          # core RDF
helpers/                  # forms, grids, translation helpers
install/                  # installer
scripts/                  # taoUpdate, taoInstall, tools, ai-notes-gc.sh
migrations/               # DB migrations
config/default/           # service defaults
views/                    # shell UI, AMD, templates, npm, Grunt
  js/controller/          # page controllers + routes.js
  js/layout/              # shell chrome
  js/loader/              # tao.min.js / vendor*.min.js (generated)
  templates/              # layout.tpl, client_config.tpl, …
test/                     # PHPUnit unit + integration
.githooks/                # optional local hooks (core.hooksPath)
```

Important entrypoints:

- `manifest.php` — extension registration (no `Extension.php`)
- `views/templates/layout.tpl` + `client_config.tpl` — shell HTML + `require.config` (maps `'ui'` → tao-core-ui)
- `views/js/controller/backoffice.js` — main backoffice AMD entry
- `scripts/taoUpdate.php` — platform update (high blast radius)

## UI layer

| Surface | Own? | Where |
|---------|------|--------|
| Backoffice shell / login / users / settings | **Yes** | `views/js/layout/*`, `views/js/controller/*` |
| Shared widgets under AMD `ui/*` | **Host / pin version** | Implement upstream in `@oat-sa/tao-core-ui`; pin via `views/package.json` |
| QTI Creator / item-test runners / domain libraries | **No** | Sibling extensions / npm runner packages |

Boot (typical backoffice page):

1. PHP action renders `.tpl` with AMD loader
2. Prod: `loader/tao.min.js`; non-prod: module loader
3. `require.config` from `client_config.tpl` (includes `'ui'` map)
4. Extension `controller/routes.js` selects the AMD controller for the action

## Conventions

- PHP controllers / `actions/` stay thin; business logic in `models/classes/` (or helpers where that is already the pattern).
- Adding or changing a backoffice screen: keep `structures.xml`, `views/js/controller/routes.js`, and the PHP action/template in sync.
- **Do not hand-edit** `views/js/loader/*.min.js` — change sources and rebuild with Grunt.
- Treat `client_config.tpl` / client config maps as load-bearing.
- FE module system is **RequireJS AMD** + **Grunt**, not a modern SPA toolchain.
- i18n: follow existing `__()` / locale patterns; do not hardcode user-facing strings where neighbors use i18n.
- RDF / ontology changes need matching install/update registration — no orphan `.rdf`.

## Testing

- Prefer **TDD**: failing unit test first, then implementation.
- PHPUnit tests live under `test/unit` and `test/integration`; base helper `test/TaoPhpUnitTestRunner.php`.
- Suite config for the installed stack is typically `nextgen-stack/tao/phpunit.xml.dist` (bootstrap via `generis`).
- Cover happy path and failure path in isolated unit tests when the surrounding suite already works that way (no live platform / external services).
- PR CI runs `oat-sa/tao-extension-ci-action` — see workflow matrix for PHP versions; do not skip or weaken that gate.
- FE: when changing client JS, run or update the nearest QUnit coverage via Grunt; do not treat missing tests as a reason to skip verification.

## Commands

From the **installed stack** root (`nextgen-stack/tao/`), after dependencies are installed — paths are relative to that root (`tao/` = this package):

```bash
# PHP — unit tests for this extension (adjust filter as needed)
./vendor/bin/phpunit -c phpunit.xml.dist tao/test/unit
```

From **this package root** (`nextgen-stack/tao/tao/`), the same suite target is `test/unit` (still use the stack `phpunit.xml.dist` and `vendor/bin/phpunit` from `nextgen-stack/tao/`).

From this package’s FE toolchain (`tao/views/build/`). Prefer **`npx grunt …`** first; npm script aliases are secondary:

```bash
npm ci
npx grunt taobundle --extension=tao
npx grunt taosass --extension=tao
npx grunt eslint:extensionreport --extension=tao --force
npx grunt taotest --extension=tao
# equivalents: npm run bundle|sass|lint|test -- --extension tao
```

Reinstall FE deps from `tao/views/` when `views/package.json` changes (`npm install`). Read current package versions from that file — do not copy them into notes.

## Hard rules / Constraints

- Keep changes inside this package unless the task explicitly requires another repo.
- Do not confuse platform core ownership with **`generis`** (kernel below) or sibling domain extensions (Items / Tests / QTI / …).
- Shared AMD `ui/*` widgets: change the owning npm package, not by copying UI into this or other extensions.
- `scripts/taoUpdate.php` / installer / migrations: high blast radius; follow nearest existing pattern; keep reversible where that is the local norm.
- Resolve PHP and dependency versions from `composer.json` and CI workflows — do not invent pins.
- Never commit `.ai/` or `.cursor/` contents.

## Agent notes (`.ai/`)

Local, **gitignored** working notes for the current branch. Do **not** commit `.ai/`. Durable forever-rules stay in this `AGENTS.md`.

**What to write here**

- A **polar-star** (north-star) note for the active branch/epic: goal, non-goals, acceptance criteria, decisions already made.
- Supporting docs: spike findings, API/UI maps, open questions, checklists, links to PRs/tickets.
- Prefer **writing decisions into `.ai/` files** and re-reading them on later turns — do not rely only on chat memory or ephemeral context. When resuming work, open `.ai/current` (or `work/<slug>/`) first and continue from those notes.

Layout:

```text
.ai/work/<branch-slug>/   # active notes (injective: `%`→`%25`, `_`→`%5F`, `/`→`_`)
.ai/current                # symlink to the active work dir
.ai/archive/*.tar.gz        # archived when the local branch no longer exists
```

Suggested files under `work/<slug>/`: `POLAR-STAR.md`, `INDEX.md`, plus topic notes as needed.

Enable checkout hooks once per clone (creates/switches the work dir and GCs orphans):

```bash
git config core.hooksPath .githooks
```

After `git branch -d` / prune:

```bash
scripts/ai-notes-gc.sh
```

Optional: `scripts/ai-notes-gc.sh --self-test`. Long-lived slugs `develop` / `master` / `main` are not auto-archived.

## Readiness gate (before “done” / before opening a PR)

Especially when an **agent** prepares a pull request, the readiness gate is a **must-have**.

### Preferred — shared skill `pr-ready-gate`

Load and follow **`pr-ready-gate`** from [oat-sa/skills](https://github.com/oat-sa/skills) (priority over inventing a local procedure).

**While this skill is under test**, use the branch tip (not `main` yet):

- Skill tree: https://github.com/oat-sa/skills/tree/feat/pr-ready-gate/pr-ready-gate
- Install (pin the branch): `gh skill install oat-sa/skills pr-ready-gate --pin feat/pr-ready-gate`
- Preview: `gh skill preview oat-sa/skills pr-ready-gate` (after the skill is visible on that ref)

After the skill lands on `main` / a release tag, switch the pin to `main` or a semver tag and drop the branch URL.

### Fallback — if the skill is unavailable

If `pr-ready-gate` cannot be loaded (not installed, `gh skill` missing, network, etc.), apply this inline gate. Treat the change as ready only when **all** of the following pass:

1. **Tests** — relevant PHPUnit (and FE tests if JS changed); prefer TDD evidence (tests added/updated with the change).
2. **Lint** — PHP/FE lint for the touched scope (Grunt eslint / project PHP QA as applicable).
3. **CodeRabbit** — local review (`coderabbit review` / project review script) with **zero critical and zero major** findings; fix or document skips for lower severities.
4. Report real command outputs — do not claim green without running the checks.

Docs-only or gitignore/hooks-only changes: run the checks that still apply (`bash -n` on shell, CodeRabbit on the diff); skip irrelevant suites explicitly.

## Skills ([oat-sa/skills](https://github.com/oat-sa/skills))

Shared agent skills for OAT live in **[oat-sa/skills](https://github.com/oat-sa/skills)**. That repository is the **priority** source:

1. **Search / load skills from `oat-sa/skills` first** when a task matches an existing skill (procedures, tooling, review loops, etc.).
2. Prefer reusing or extending those shared skills over inventing a parallel local skill.
3. Create a **new** skill only when nothing suitable exists there (and in this repo) — and consider contributing it upstream to `oat-sa/skills` when it is reusable beyond this package.

**Must-have for implementation / PR prep:** [`pr-ready-gate`](https://github.com/oat-sa/skills/tree/feat/pr-ready-gate/pr-ready-gate) (branch pin while testing — see Readiness gate). Do not duplicate that skill’s full procedure here; use the fallback section above only when the skill cannot be loaded.

Do not duplicate other long procedures in this `AGENTS.md` when a shared skill already covers them; link or name the skill instead.

## Pointers

- `README.md` — package overview
- `composer.json` / `LICENSE` — license and Composer deps
- `views/package.json` — FE dependency pins
- [oat-sa/skills](https://github.com/oat-sa/skills) — shared / priority agent skills
- [`pr-ready-gate`](https://github.com/oat-sa/skills/tree/feat/pr-ready-gate/pr-ready-gate) — must-have readiness gate (branch pin while testing)
- `.coderabbit.yaml` → remote `oat-sa/tao-code-quality` `coderabbit/php/authoring/v1`
- `.github/workflows/continuous-integration.yaml` — PR CI on `develop`
- `.githooks/post-checkout` + `scripts/ai-notes-gc.sh` — local `.ai/` lifecycle
- Local architecture notes (not in this git repo): `nextgen-stack/.vscode/agent-notes/tao-architecture/tao-core.md`

## Default Agent Behavior

1. Read this file, then `.ai/current` / polar-star notes for the branch; prefer written notes over chat memory.
2. Check **[oat-sa/skills](https://github.com/oat-sa/skills)** for a matching skill before inventing a new procedure or local skill.
3. Prefer TDD for behavior changes.
4. Keep the change minimal and local; resolve versions from composer / package.json / CI files.
5. Sync `structures.xml` / `routes.js` / PHP when touching UI entrypoints.
6. Update license years on touched files; add sibling-style headers on new files (`GPL-2.0-only` policy via `composer.json`).
7. Update `.ai/` polar-star / supporting docs as decisions land.
8. Satisfy the Readiness gate before calling the work done or opening a PR — prefer skill `pr-ready-gate` (branch pin while testing); otherwise the inline fallback in this file.
9. Do not weaken CI / lint gates.
