const { defineConfig } = require("cypress");
const { installPlugin } = require("@chromatic-com/cypress");

module.exports = defineConfig({
  e2e: {
    // baseUrl is set using environment variables because it differs between
    // development and CI setups.
    setupNodeEvents(on, config) {
      installPlugin(on, config);
    },
  },
  env: {
    // Tests should take or enable screenshots themselves.
    disableAutoSnapshot: true,
    // Set a high timeout to allow resources to download.
    resourceArchiveTimeout: 10000,
    // Environment variables for services are set in docker-compose.ci.yml for
    // CI and Taskfile.yml for local development.
  },
});
