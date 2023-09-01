# E2E testing

Development of end-to-end tests in this project is based on the principle of storing the test specs, fixtures and environment configs here, while some shared parts are imported. The imported parts are the Cypress binary, plugins and shared support code, which are all provided by the dependency `@oat-sa/e2e-runner`.

The local structure is a reduced form of the classic Cypress project structure:

<pre>
|-- cypress.json        # project config
|-- cypress/            #
  |-- envs/             # environment configs
  |-- fixtures/         # static data used in tests
  |-- tests/            # root folder of the tests
  |-- plugins/          # folder for the plugins
  |-- support/          # support commands, imports, global setup
</pre>

## Configuration

Because tests may be run against various envs (local, demo, staging, etc), we need to have multiple env files. They are stored in `cypress/envs/`, and loaded into the main config according to the key `env.configFile` defined in the `cypress.json`.

Create `envs/env*.json` file and set it in the `cypress.json`:

```json
{
    "env": {
        "configFile": "cypress/envs/env-local.json"
    }
}
```

## Commands

[Commands](https://docs.cypress.io/api/cypress-api/custom-commands.html) are a key part of Cypress. For now commands can be registered to `Cypress.Commands` in `cypress/support/commands` file.
There's no ability to register them within the extensions yet.

> When registering a local or global command, take care to avoid name collisions with any command you might have imported.

## Plugins

Plugins can be created in `cypress/plugins` directory.
Some plugins also register commands. You can import these files (for their side effects) in the `cypress/support/index.js`.

Example:

```js
// cypress/support/index.js
import '@cypress/skip-test/support';
```
There's no ability to add plugins in the extensions yet.

## Fixtures

Any data needed in local tests (and not hard-coded) should be placed in `cypress/fixtures/`. Can be JSON, JavaScript, zip files, whatever is needed.
There's no ability to add fixtures in the extensions yet.


## How to run the tests

To run the tests there's a single entry point in tao core.

In your tao installation folder:
* `cd tao/views`
* `npm install`
* `npm run cy:open`  - to open cypress UI and browser
    
    or
    
   `npm run cy:run` - to run the tests headless
   
## How to create your tests

Add .spec files to the `views/cypress/tests` folder of an appropriate extension.
> Remember to place the tests to corresponding extension.

> Feel free to use common commands from the tao core (located in `tao/views/cypress/support`)

> The tests should not rely on the interface text because different environments may have different language settings.