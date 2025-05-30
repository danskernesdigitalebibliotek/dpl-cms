import '@testing-library/cypress/add-commands';
import { WireMockRestClient } from 'wiremock-rest-client';
import { Options } from 'wiremock-rest-client/dist/model/options.model';
import { StubMapping } from 'wiremock-rest-client/dist/model/stub-mapping.model';
import { RequestPattern } from 'wiremock-rest-client/dist/model/request-pattern.model';

const wiremock = (baseUri?: string, options?: Options) => {
  return new WireMockRestClient(
    baseUri || Cypress.env('WIREMOCK_URL'),
    options,
  );
};

Cypress.Commands.add('createMapping', (stub: StubMapping) => {
  cy.wrap(wiremock().mappings.createMapping(stub));
});

Cypress.Commands.add('resetMappings', () => {
  cy.wrap(wiremock().mappings.resetAllMappings());
});

Cypress.Commands.add('logMappingRequests', () => {
  cy.wrap(
    wiremock()
      .mappings.getAllMappings()
      .then((mappings) => {
        Cypress.log({
          name: 'Wiremock',
          message: `Mappings: ${mappings.meta.total}`,
        });
        mappings.mappings.forEach((stub) => {
          wiremock()
            .requests.getCount(stub.request)
            .then((request: { count: number }) => {
              const requestUrlPath =
                stub.request.url ||
                stub.request.urlPattern ||
                stub.request.urlPath ||
                stub.request.urlPathPattern;
              Cypress.log({
                name: 'Wiremock',
                message: `${stub.request.method}: ${requestUrlPath}: ${request.count} hit(s)`,
              });
            });
        });
      }),
  );
});

Cypress.Commands.add('getRequestCount', (request: RequestPattern) => {
  cy.wrap(
    wiremock()
      .requests.getCount(request)
      .then((response: { count: number }) => {
        return response.count;
      }),
  );
});

Cypress.Commands.add('resetRequests', () => {
  cy.wrap(wiremock().requests.resetAllRequests());
});

Cypress.Commands.add('logRequests', () => {
  cy.wrap(
    wiremock()
      .requests.getAllRequests()
      .then((data) => {
        data.requests.forEach((requestResponse) => {
          const request = requestResponse.request;
          Cypress.log({
            name: 'Wiremock',
            message: `${request.method}: ${request.url}`,
          });
        });
      }),
  );
});

Cypress.Commands.add('drupalLogin', (url?: string) => {
  const username = Cypress.env('DRUPAL_USERNAME');
  const password = Cypress.env('DRUPAL_PASSWORD');
  cy.session({ username, password }, () => {
    cy.visit('/user/login');

    // If the CookieInformation prompt is here, we want to click it, to not
    // have it block the user information.
    cy.get('body').then(($body) => {
      if ($body.find('.coi-banner__accept').length > 0) {
        cy.get('.coi-banner__accept').first().click();
      }
    });

    cy.get('[name="name"]')
      .type(username)
      .parent()
      .get('[name="pass"]')
      .type(password);
    cy.get('.button-login').click();

    cy.visit('/user/edit');

    // Making sure the required author field is filled out.
    cy.get('[name="field_author_name[0][value]"]').clear().type(username);

    // Making sure the interface language is set to english, to simplify our
    // tests using "contains".
    cy.get('[data-drupal-selector="edit-preferred-langcode"]').select('en');
    cy.get('[data-drupal-selector="edit-preferred-admin-langcode"]').select(
      'en',
    );
    cy.get('[data-drupal-selector="edit-submit"]').click();
  });

  if (url) {
    cy.visit(url);
  }
});

Cypress.Commands.add('anonymousUser', () => {
  cy.session('anonymous', () => {});
});

Cypress.Commands.add('drupalLogout', () => {
  cy.visit('/logout');
});

Cypress.Commands.add('drupalCron', () => {
  // Because we run Wiremock as a proxy only services configured with the
  // proxy will use it. We need to proxy requests during cron and only the
  // web container is configured to use the proxy, and thus we have to run
  // cron through the web frontend. Using the proxy with the CLI container would
  // cause too many irrelevant requests to pass through the proxy.
  cy.drupalLogin();
  cy.visit('/admin/config/system/cron');
  cy.get('[value="Run cron"]').click();
  cy.contains('Cron ran successfully.');
  cy.drupalLogout();
});

const adgangsplatformenLoginOauthMappings = ({
  userIsAlreadyRegistered,
  authorizationCode,
  accessToken,
  userCPR,
  userGuid,
}: {
  userIsAlreadyRegistered: boolean;
  authorizationCode: string;
  accessToken: string;
  userCPR?: number;
  userGuid?: string;
}) => {
  cy.createMapping({
    request: {
      method: 'GET',
      urlPath: '/oauth/authorize',
      queryParameters: {
        response_type: {
          equalTo: 'code',
        },
      },
    },
    response: {
      status: 302,
      headers: {
        location: `{{request.query.[redirect_uri]}}?code=${authorizationCode}&state={{request.query.[state]}}`,
      },
      transformers: ['response-template'],
    },
  });

  cy.createMapping({
    request: {
      method: 'POST',
      urlPath: '/oauth/token/',
      bodyPatterns: [
        {
          contains: 'grant_type=authorization_code',
        },
        {
          contains: `code=${authorizationCode}`,
        },
      ],
    },
    response: {
      jsonBody: {
        access_token: accessToken,
        token_type: 'Bearer',
        expires_in: 2591999,
      },
    },
  });

  cy.createMapping({
    request: {
      method: 'GET',
      urlPath: '/userinfo/',
      headers: {
        Authorization: {
          equalTo: `Bearer ${accessToken}`,
        },
      },
    },
    response: {
      jsonBody: {
        attributes: {
          cpr: userCPR,
          ...(userGuid ? { uniqueId: userGuid } : {}),
        },
      },
    },
  });

  const patronBody = (userIsAlreadyRegistered: boolean) => {
    return {
      authenticateStatus: userIsAlreadyRegistered ? 'VALID' : 'INVALID',
      patron: {
        // This is not a complete patron object but with regards to login/register we only need to ensure an empty blocked
        // status so we leave out all other information.
        blockStatus: [],
      },
    };
  };

  cy.createMapping({
    request: {
      method: 'GET',
      urlPath: '/external/agencyid/patrons/patronid/v4',
      headers: {
        Authorization: {
          equalTo: `Bearer ${accessToken}`,
        },
      },
    },
    response: {
      jsonBody: patronBody(userIsAlreadyRegistered),
    },
  });
};

Cypress.Commands.add(
  'adgangsplatformenLogin',
  ({
    authorizationCode,
    accessToken,
    userCPR,
    userGuid,
  }: {
    authorizationCode: string;
    accessToken: string;
    userCPR?: number;
    userGuid?: string;
  }) => {
    cy.session({ authorizationCode, accessToken, userCPR, userGuid }, () => {
      adgangsplatformenLoginOauthMappings({
        userIsAlreadyRegistered: true,
        authorizationCode,
        accessToken,
        userCPR,
        userGuid,
      });

      cy.visit('/user/login');
      cy.contains('Log in with Adgangsplatformen').click();
    });
  },
);

Cypress.Commands.add(
  'setupAdgangsplatformenRegisterMappinngs',
  ({
    authorizationCode,
    accessToken,
    userCPR,
    userGuid,
  }: {
    authorizationCode: string;
    accessToken: string;
    userCPR?: number;
    userGuid?: string;
  }) => {
    adgangsplatformenLoginOauthMappings({
      userIsAlreadyRegistered: false,
      authorizationCode,
      accessToken,
      userCPR,
      userGuid,
    });
    // Patron creation
    cy.createMapping({
      request: {
        method: 'POST',
        urlPattern: '.*/external/agencyid/patrons/v9',
      },
      response: {
        jsonBody: {
          authenticated: true,
          patron: {},
        },
      },
    });
    cy.createMapping({
      request: {
        method: 'GET',
        // TODO: Create more exact urlPatterns
        urlPattern: '.*/fees.*',
      },
      response: {
        jsonBody: {},
      },
    });
    cy.createMapping({
      request: {
        method: 'GET',
        urlPattern: '.*/reservations.*',
      },
      response: {
        jsonBody: {},
      },
    });
    cy.createMapping({
      request: {
        method: 'GET',
        urlPattern: '.*/loans.*',
      },
      response: {
        jsonBody: {},
      },
    });
  },
);

Cypress.Commands.add(
  'verifyToken',
  ({
    token,
    tokenType,
  }: {
    tokenType: 'library' | 'user' | 'unregistered-user';
    token: string;
  }) => {
    cy.request('/dpl-react/user-tokens')
      .its('body')
      .should(
        'contain',
        `window.dplReact.setToken("${tokenType}", "${token}")`,
      );
  },
);

const visible = (checkVisible: boolean) => (checkVisible ? ':visible' : '');
Cypress.Commands.add('getBySel', (selector, checkVisible = false, ...args) => {
  return cy.get(`[data-cy="${selector}"]${visible(checkVisible)}`, ...args);
});

Cypress.Commands.add('clickSaveButton', () => {
  cy.get('#edit-gin-sticky-actions input[value="Save"]').click();
});

Cypress.Commands.add('deleteEntitiesIfExists', (name) => {
  const formattedSearchString = name.toLowerCase().replace(/ /g, '+');

  cy.drupalLogin();
  cy.visit(
    `/admin/content?title=${formattedSearchString}&status=All&langcode=All`,
  );

  cy.get('tbody').then((tbody) => {
    if (tbody.find('td.views-empty').length) {
      cy.log('No branches to delete.');
    } else {
      cy.get('input[title="Select all rows in this table"]').check({
        force: true,
      });
      cy.get('#edit-action').select('node_delete_action');
      cy.contains('input', 'Apply to selected items').click();
      cy.contains('input', 'Delete').click();
    }
  });
});

Cypress.Commands.add('openParagraphsModal', () => {
  cy.get('button[title="Show all Paragraphs"]').click();
});

// According to the documentation of types and Cypress commands
// the namespace is declared like it is done here. Therefore we'll bypass errors about it.
/* eslint-disable @typescript-eslint/no-namespace */
declare global {
  namespace Cypress {
    interface Chainable {
      createMapping(stub: StubMapping): Chainable<null>;
      resetMappings(): Chainable<null>;
      logMappingRequests(): Chainable<null>;
      logRequests(): Chainable<null>;
      getRequestCount(request: RequestPattern): Chainable<number>;
      resetRequests(): Chainable<null>;
      anonymousUser(): Chainable<null>;
      drupalLogin(url?: string): Chainable<null>;
      drupalLogout(): Chainable<null>;
      drupalCron(): Chainable<null>;
      adgangsplatformenLogin(params: {
        authorizationCode: string;
        accessToken: string;
        userCPR?: number;
        userGuid?: string;
      }): Chainable<null>;
      setupAdgangsplatformenRegisterMappinngs(params: {
        authorizationCode: string;
        accessToken: string;
        userCPR?: number;
        userGuid?: string;
      }): Chainable<null>;
      verifyToken(params: {
        tokenType: 'library' | 'user' | 'unregistered-user';
        token: string;
      }): Chainable<null>;
      getBySel(
        selector: string,
        checkVisible?: boolean,
        ...args: unknown[]
      ): Chainable;
      deleteEntitiesIfExists(name: string): Chainable<null>;
      clickSaveButton(): Chainable<null>;
      openParagraphsModal(): Chainable<null>;
    }
  }
}
