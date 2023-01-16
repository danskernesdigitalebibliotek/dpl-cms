/* eslint-disable @typescript-eslint/no-var-requires */
const autosuggestData = require("../../wiremock/src/mappings/search/data/fbi/autosugggest.json");
const searchResultData = require("../../wiremock/src/mappings/search/data/fbi/searchWithPagination.json");

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

    cy.visit("/search?q=Harry%2520Potter")
      .get('[data-cy="search-result-title"]')
      .should("contain", "Showing results for “Harry Potter” (109)")
      .get('[data-cy="search-result-list"]')
      .children()
      .eq(0)
      .click()
      .url()
      .should("include", "work/work-of:870970-basis:25245784");
  });

  beforeEach(() => {
    cy.resetMappings();
  });
});
