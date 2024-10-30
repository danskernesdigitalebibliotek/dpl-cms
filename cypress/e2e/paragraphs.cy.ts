const pageName = "Test page";

const createTestPageAndOpenParagraphModal = () => {
  cy.drupalLogin("/node/add/page");
  cy.findByLabelText("Title").type(pageName);
  cy.openParagraphsModal();
};

const addParagraph = (paragraphType: string) => {
  cy.get(`button[value="${paragraphType}"]`).click({
    multiple: true,
    force: true,
  });
};

// INSPRIATION: https://drupal.stackexchange.com/questions/315635/how-to-write-in-a-ckdeditor-textarea-with-cypress
const typeInCkEditor = (content: string) => {
  // Ensure the CKEditor is visible
  cy.get(".ck-editor__editable").should("be.visible");

  cy.window().then((win) => {
    // @ts-expect-error - Drupal may not exist on the window object
    if (win.Drupal && win.Drupal.CKEditor5Instances) {
      // @ts-expect-error - CKEditor5Instances may not match expected structure
      win.Drupal.CKEditor5Instances.forEach(
        (editor: { setData: (content: string) => void }) => {
          editor.setData(content);
        }
      );
    }
  });
};

describe("Paragraph module", () => {
  beforeEach(() => {
    cy.deleteAllContentIfExists(pageName, "page");
    createTestPageAndOpenParagraphModal();
  });

  it("Can add 'Text body' (CEK editor)", () => {
    addParagraph("Text body");

    typeInCkEditor("Hello, world!");

    cy.saveContent();

    cy.get(".rich-text").should("contain", "Hello, world!");
  });
});
