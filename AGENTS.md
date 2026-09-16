# AGENTS.md — tao-core

## Purpose

`oat-sa/tao-core` is the TAO **back-office platform core** (extension id `tao`): auth, ACL, routing, task queue, install/update, client config, and the shell UI that other extensions mount into.

It is **not** the domain home for Items, Tests, QTI, Media, Delivery, or Proctoring — those live in sibling extensions. Shared UI widgets consumed via AMD alias `ui` are provided by **`@oat-sa/tao-core-ui`**, which this package hosts through `views/package.json` — do not fork that UI into domain extensions.

### Shared agent conventions (for sibling extensions)

This `AGENTS.md` is also the **canonical home** for cross-extension agent rules used by TAO community PHP packages, including:

- Context budget / search discipline
- Definition of Done
- Family anti-patterns (generated bundles, wrong-package edits, weakening gates)
- Verify-by-change-type matrix
- Readiness gate + [`pr-ready-gate`](https://github.com/oat-sa/skills/tree/feat/pr-ready-gate/pr-ready-gate) / inline fallback
- Local `.ai/` notes lifecycle (hooks + GC pattern)

Sibling extension `AGENTS.md` files should stay **isolated**: package-specific purpose, ownership, structure, and commands only. For the shared rules above, point agents at the installed **`tao`** package (`oat-sa/tao-core`) `AGENTS.md` — do **not** depend on any monorepo checkout (e.g. nextgen-stack) or workstation-only note paths.

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

## Context budget

Spend context on the smallest useful surface. Prefer accuracy over exhaustive reading.

1. **Read order:** this file → `.ai/current` / polar-star → only paths implicated by the task (use Structure / UI layer maps).
2. **Do not** load or search wholesale: `vendor/`, `node_modules/`, `views/js/loader/*.min.js`, or unrelated sibling extensions in the installed platform.
3. **Search narrowly** (symbol / filename / nearby tests) before broad repo greps.
4. **One concern per change.** No drive-by refactors, unrelated formatting, or “while we’re here” edits in other packages.
5. Prefer writing durable facts into `.ai/` and re-reading them over re-discovering the same tree every turn.

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
- Run PHPUnit from the **installed TAO platform** (Composer application root that contains `vendor/` and usually a root `phpunit.xml.dist` with generis bootstrap) — not from a bare clone of this repo alone unless that clone is the platform root.
- Do **not** assume a particular monorepo name or path (e.g. a local “nextgen-stack” checkout). Discover the platform root from the environment: directory that holds `vendor/bin/phpunit` and installs this package as the `tao` extension.
- Cover happy path and failure path in isolated unit tests when the surrounding suite already works that way (no live platform / external services).
- PR CI runs `oat-sa/tao-extension-ci-action` — see workflow matrix for PHP versions; do not skip or weaken that gate.
- FE: when changing client JS, run or update the nearest QUnit coverage via Grunt; do not treat missing tests as a reason to skip verification.

## Commands

**Platform root** = the Composer application that installs this package (has `vendor/`, platform `phpunit.xml.dist`). **Package root** = this git repository (extension id `tao`). Paths below use `tao/` as the extension directory name inside a typical platform install — adjust if your install maps the package elsewhere.

From the **platform root**, after dependencies are installed:

```bash
# PHP — unit tests for this extension (adjust filter as needed)
./vendor/bin/phpunit -c phpunit.xml.dist tao/test/unit
```

From **this package root**, the suite directory is `test/unit` (still invoke `vendor/bin/phpunit` and the platform `phpunit.xml.dist` from the platform root).

FE toolchain usually lives under the platform’s `tao/views/build/` (or this package’s `views/build/` when present). Prefer **`npx grunt …`** first; npm script aliases are secondary:

```bash
npm ci
npx grunt taobundle --extension=tao
npx grunt taosass --extension=tao
npx grunt eslint:extensionreport --extension=tao --force
npx grunt taotest --extension=tao
# equivalents: npm run bundle|sass|lint|test -- --extension tao
```

Reinstall FE deps from the package `views/` when `views/package.json` changes (`npm install`). Read current package versions from that file — do not copy them into notes.

## Hard rules / Constraints

- Keep changes inside this package unless the task explicitly requires another repo.
- Do not confuse platform core ownership with **`generis`** (kernel below) or sibling domain extensions (Items / Tests / QTI / …).
- Shared AMD `ui/*` widgets: change the owning npm package, not by copying UI into this or other extensions.
- `scripts/taoUpdate.php` / installer / migrations: high blast radius; follow nearest existing pattern; keep reversible where that is the local norm.
- Resolve PHP and dependency versions from `composer.json` and CI workflows — do not invent pins.
- Never commit `.ai/` or `.cursor/` contents.

## Anti-patterns

Do **not**:

- Hand-edit `views/js/loader/*.min.js` or other Grunt-generated bundles.
- Fork or copy AMD `ui/*` widgets into this package — change `@oat-sa/tao-core-ui` (or the owning package) and pin here.
- Patch Items / Tests / QTI / Media / Delivery / Proctoring “because they are next door” when the bug belongs in those packages.
- Invent dependency or PHP version pins; read `composer.json`, `views/package.json`, and CI workflows.
- Weaken, skip, or silence CI / lint / PHPUnit / CodeRabbit gates to land a change.
- Mark work done or open a PR while Readiness gate (or `pr-ready-gate`) still fails.
- Rely only on chat memory for branch decisions — update `.ai/` polar-star / notes instead.
- Auto-migrate license headers to SPDX dual-license; keep sibling-style **`GPL-2.0-only`** headers.

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

## Verify by change type

Run the **narrowest** checks that still match CI intent. Always state what you skipped and why.

| Change type | Must run | Usually skip |
|-------------|----------|--------------|
| PHP behavior / bugfix | Focused PHPUnit for touched area; Readiness / `pr-ready-gate` | Full FE grunt suite |
| FE JS / AMD / templates | `npx grunt eslint:extensionreport --extension=tao` (or scoped eslint); nearest QUnit / `taotest` when coverage exists; rebuild bundles if sources that feed `loader/*.min.js` changed; Readiness / `pr-ready-gate` | Unrelated PHPUnit packages |
| Sass / styles only | `npx grunt taosass --extension=tao`; lint if project expects it; Readiness on the diff | PHPUnit |
| Docs / `.gitignore` / hooks / `AGENTS.md` only | `bash -n` on touched shell; CodeRabbit on the diff | Full PHPUnit / FE suites (say so explicitly) |
| Installer / migrations / `taoUpdate` | Follow nearest script tests if any; extra care review; Readiness / `pr-ready-gate` | Untouched FE bundles |

If tooling is missing locally, report that and **do not** claim the gate passed.

## Definition of Done

Work is done (and PR-ready when asked) only when **all** apply:

1. Task / AC / polar-star acceptance criteria addressed (or gaps listed for the user).
2. Diff is minimal and local to this package unless the task required otherwise.
3. Behavior changes have TDD evidence (failing test first, or an explicit docs/config-only exception).
4. License headers / years updated on touched or new files (`GPL-2.0-only` sibling style).
5. `.ai/` polar-star / notes updated with decisions that matter for the next turn.
6. **Readiness gate** satisfied via `pr-ready-gate` (or the inline fallback below).
7. Real command results reported — no “should be green” without running the checks.

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
- Optional local overviews may exist outside this repo (developer workstation notes) — never required to open or edit this package

## Default Agent Behavior

1. Read this file, then `.ai/current` / polar-star notes for the branch; prefer written notes over chat memory.
2. Obey **Context budget** — narrow reads/searches; one concern per change.
3. Check **[oat-sa/skills](https://github.com/oat-sa/skills)** for a matching skill before inventing a new procedure or local skill.
4. Prefer TDD for behavior changes.
5. Keep the change minimal and local; resolve versions from composer / package.json / CI files.
6. Sync `structures.xml` / `routes.js` / PHP when touching UI entrypoints.
7. Avoid **Anti-patterns**; update license years on touched files (`GPL-2.0-only` sibling style).
8. Update `.ai/` polar-star / supporting docs as decisions land.
9. Verify using the **change-type** matrix; then satisfy **Definition of Done** + Readiness (`pr-ready-gate` or inline fallback).
10. Do not weaken CI / lint gates.
