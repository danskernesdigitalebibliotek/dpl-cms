const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      require("cypress-terminal-report/src/installLogsPrinter")(on);
    },
    // baseUrl is set using environment variables because it differs between
    // development and CI setups.
  },
  env: {
    // This is intentionally left empty.
    // Environment variables for services are set in docker-compose.ci.yml for
    // CI and Taskfile.yml for local development.
  },
});
