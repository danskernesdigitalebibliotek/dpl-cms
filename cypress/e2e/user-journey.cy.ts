/* eslint-disable @typescript-eslint/no-var-requires */
const autosuggestData = require("../../wiremock/src/mappings/search/data/fbi/autosugggest.json");
const searchResultData = require("../../wiremock/src/mappings/search/data/fbi/searchWithPagination.json");
const availabilityLabelsData = require("../../wiremock/src/mappings/work/data/fbs/availability.json");
const workData = require("../../wiremock/src/mappings/work/data/fbi/getMaterial.json");
const workHoldingData = require("../../wiremock/src/mappings/work/data/fbs/holdings.json");
const patronData = require("../../wiremock/src/mappings/work/data/fbs/patron.json");
const reservationData = require("../../wiremock/src/mappings/work/data/fbs/reservation.json");
const reservationAvailability = require("../../wiremock/src/mappings/work/data/fbs/reservationAvailability.json");

describe("User journey", () => {
  it("Shows search suggestions & redirects to search result page", () => {
    cy.createMapping({
      request: {
        method: "POST",
        url: "/opac/graphql",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: autosuggestData,
      },
    });

    cy.visit("/")
      .get(".header__menu-search-input")
      .focus()
      .type("harry")
      .get(".autosuggest")
      .should("be.visible")
      .get(".autosuggest__text")
      .eq(0)
      .click()
      .url()
      .should("include", "search?q=Harry%2520Potter");
  });

  it("Shows search results & redirects to material page", () => {
    cy.createMapping({
      request: {
        method: "POST",
        url: "/opac/graphql",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: searchResultData,
      },
    });
    cy.createMapping({
      request: {
        method: "GET",
        urlPattern: ".*/availability/v3.*",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: availabilityLabelsData,
      },
    });

    cy.visit("/search?q=Harry%2520Potter")
      .get("[data-cy='search-result-title']")
      .should("contain", "Showing results for “Harry Potter” (109)")
      .get("[data-cy='search-result-list']")
      .children()
      .eq(0)
      .click()
      .url()
      .should("include", "work/work-of:870970-basis:25245784");
  });

  function mockMaterialPage() {
    cy.createMapping({
      request: {
        method: "POST",
        url: "/opac/graphql",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: workData,
      },
    });
    cy.createMapping({
      request: {
        method: "GET",
        urlPattern: ".*/availability/v3.*",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: availabilityLabelsData,
      },
    });
    cy.createMapping({
      request: {
        method: "GET",
        urlPattern: ".*/holdings/v3.*",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: workHoldingData,
      },
    });
    cy.createMapping({
      request: {
        method: "GET",
        urlPattern: ".*/patrons/patronid/v2",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: patronData,
      },
    });
  }

  it("Shows material page & can open reservation modal", () => {
    mockMaterialPage();

    cy.visit("/work/work-of:870970-basis:25245784")
      .contains("Harry Potter og Fønixordenen")
      .get("[data-cy='material-header-buttons-physical']")
      .should("contain", "Reserve bog")
      .click()
      .get("[data-cy='modal']")
      .should("contain", "Harry Potter og Fønixordenen");
  });

  it("Can reserve a material & shows a confirmation screen", () => {
    mockMaterialPage();

    cy.visit("/work/work-of:870970-basis:25245784")
      .get("[data-cy='material-header-buttons-physical']")
      .click()
      .get("[data-cy='modal']")
      .should("contain", "Harry Potter og Fønixordenen");

    cy.createMapping({
      request: {
        method: "POST",
        urlPattern: ".*/reservations/v2",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: reservationData,
      },
    });
    cy.createMapping({
      request: {
        method: "GET",
        urlPattern: ".*/availability/v3.*",
      },
      response: {
        headers: {
          "Content-Type": "application/json",
        },
        status: 200,
        jsonBody: reservationAvailability,
      },
    });

    cy.get("[data-cy='reservation-modal-submit-button']")
      .click()
      .get("[data-cy='modal']")
      .should(
        "contain",
        "The material is available and is now reserved for you!"
      )
      .get("[data-cy='reservation-success-close-button']")
      .click()
      .get("[data-cy='modal']")
      .should("not.exist");
  });

  beforeEach(() => {
    cy.resetMappings();
  });
});
