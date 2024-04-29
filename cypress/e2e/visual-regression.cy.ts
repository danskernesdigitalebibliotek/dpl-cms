// This test has a few oddities
// - Enable automatic snapshots at the end of each test by disabling the disable.
// - Scroll to bottom in each test to ensure all lazy loading images are ready.
//   afterEach() is too late.
describe("Site visuals", { env: { disableAutoSnapshot: false } }, () => {
  it("renders the frontpage", () => {
    cy.visit("/");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders the article list", () => {
    cy.visit("/articles");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders an article page", () => {
    cy.visit("/by_uuid/node/2cd0fe5e-4159-4452-86aa-e1a1ac8db4a1");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders the event list", () => {
    cy.visit("/events");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders an event series page", () => {
    cy.visit("/by_uuid/eventseries/c8177097-1438-493e-8177-e8ef968cc133");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders the branch list", () => {
    cy.visit("/branches");
    cy.scrollTo("bottom", { duration: 2000 });
  });

  it("renders an branch page", () => {
    cy.visit("/by_uuid/node/dac275e4-9b8c-4959-a13a-6b9fdbc1f6b0");
    cy.scrollTo("bottom", { duration: 2000 });
  });
});
