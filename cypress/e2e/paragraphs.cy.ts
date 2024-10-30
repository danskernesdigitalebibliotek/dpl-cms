const pageName = "Test page";

const createTestPageAndOpenParagraphModal = () => {
  cy.drupalLogin("/node/add/page");
  cy.findByLabelText("Title").type(pageName);
  cy.openParagraphsModal();
};
describe("Paragraph module", () => {
  beforeEach(() => {
    cy.deleteAllContentIfExists(pageName, "page");
    createTestPageAndOpenParagraphModal();
  });
});
