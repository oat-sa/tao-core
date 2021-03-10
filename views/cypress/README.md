# cypress folder

Development of end-to-end tests in this project is based on the principle of storing the test specs, fixtures and environment configs here, while some shared parts are imported. The imported parts are the Cypress binary, plugins and shared support code, which are all provided by the dependency `@oat-sa-private/e2e-runner`.

The local structure is a reduced form of the classic Cypress project structure:

<pre>
|-- cypress.json        # project config
|-- cypress/            #
  |-- envs/             # environment configs
  |-- fixtures/         # static data used in tests
  |-- integration/      # root folder of the tests
  |-- support/          # suppport commands, imports, global setup
</pre>

## Notes on environment configs

Because tests may be run against various envs (local, demo, staging...), we need to have multiple env files. They are stored in `cypress/envs/`, and loaded into the main config according to the key `env.configFile` defined in the main config.

> The config is extended thanks to the init function in `e2e-runner/plugins/index.js`. If, for some reason, you do not use the central plugins init, you can copy an existing env file to a new file called `cypress.env.json`, at the same level as `cypress.json`.
The env config is the place for any and all variables (urls, params, content ids) specific to the environment the tests will run against. It can be extended as needed. Two env files do not need to fully match in key names. The local env file will differ between 2 developers' machines (particularly in `deliveryId`s).

## Notes on commands

[Commands](https://docs.cypress.io/api/cypress-api/custom-commands.html) are a key part of Cypress. Commands can be registered to `Cypress.Commands` at several levels:

- locally to a spec file (it will not exist outside that scope)
- globally in your project, in `cypress/support/commands.js` (or a sibling file - extra files can be created, for organisation)
- centrally, in `e2e-runner`, if the command should be shared or re-used

> When registering a local or global command, take care to avoid name collisions with any command you might have imported.
## Notes on plugins

The local project doesn't contain plugin dependencies or any plugin setup file. Instead, the project is configured by using `cypress.json` to point to the `e2e-runner` plugins loader:

```json
{
  "pluginsFile": "node_modules/@oat-sa-private/e2e-runner/plugins"
}
```

Having a single function handling plugins init (the `export` of the above file) is simpler than trying to init on 2 levels. Plugin dependency updating is also restricted to a single place.

Some plugins also register commands. You can import these files (for their side effects) in the local `support/index.js`.

Example:

```js
// cypress/support/index.js
import '@cypress/skip-test/support';
```

## Notes on fixtures

Any data needed in local tests (and not hard-coded) should be placed in `cypress/fixtures/`. Can be JSON, JavaScript, zip files, whatever is needed.
