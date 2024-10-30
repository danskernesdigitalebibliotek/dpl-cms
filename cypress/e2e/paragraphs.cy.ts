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

const addAnotherParagraph = () => {
  cy.get("button[title='Show all Paragraphs']")
    .should("be.visible")
    .eq(1)
    .click();
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

// INSPRIATION: https://github.com/kanopi/shrubs/blob/main/mediaLibrarySelect.js
const mediaLibrarySelect = (fileName: string, index = 0) => {
  // Create unique intercepts for each media library select
  const mediaNodeEditAjax = `mediaNodeEditAjax${index}`;
  const mediaLibraryAjax = `mediaLibraryAjax${index}`;
  const viewsAjax = `viewsAjax${index}`;

  cy.intercept("POST", "/node/*/**").as(mediaNodeEditAjax);
  cy.intercept("POST", "/media-library**").as(mediaLibraryAjax);
  cy.intercept("GET", "/views/ajax?**").as(viewsAjax);

  cy.get(
    "#field_medias-media-library-wrapper-field_paragraphs-0-subform"
  ).within(() => {
    cy.get('input[value="Add media"]').click();
  });

  cy.wait(`@${mediaNodeEditAjax}`).its("response.statusCode").should("eq", 200);

  cy.get(".media-library-widget-modal").within(() => {
    cy.get('.views-exposed-form input[name="search"]').clear().type(fileName);
    cy.get('.views-exposed-form input[type="submit"]').click();
    cy.wait(`@${viewsAjax}`, { timeout: 10000 })
      .its("response.statusCode")
      .should("eq", 200);
    cy.get(".media-library-views-form .views-row").first().click();

    cy.get(".form-actions button").contains("Insert selected").click();
  });

  cy.wait(`@${mediaNodeEditAjax}`).its("response.statusCode").should("eq", 200);

  // Validate the image appears in the preview to address flakiness in GH actions.
  cy.get(".media-library-item__preview-wrapper")
    .eq(index)
    .within(() => {
      cy.get(".field--name-field-media-image img")
        .should("exist")
        .and("have.attr", "src")
        .and("include", fileName);
    });
};

type CheckImageSrcType = {
  selector: string;
  expectedInSrc: string;
};

const checkImageSrc = ({ selector, expectedInSrc }: CheckImageSrcType) => {
  cy.get(selector).should("have.attr", "src").should("include", expectedInSrc);
};

const addSimpleLink = ({ link, index = 0 }) => {
  if (index > 0) {
    cy.get(`input[value="Add another item"]`).click();
  }
  cy.findAllByLabelText("URL").eq(index).type(link.url);
  cy.findAllByLabelText("Link text").eq(index).type(link.text);
  if (link.targetBlank) {
    cy.findAllByLabelText("Open link in new window/tab").eq(index).check();
  }
};

const verifySimpleLink = ({ link, index = 0 }) => {
  cy.get(".paragraphs__item--simple_links a")
    .eq(index)
    .should("contain", link.text)
    .and("have.attr", "href", link.url);
  if (link.targetBlank) {
    cy.get(".paragraphs__item--simple_links a")
      .eq(index)
      .should("have.attr", "target", "_blank");
  }
};

describe("Paragraphs module", () => {
  beforeEach(() => {
    cy.deleteAllContentIfExists(pageName, "page");
    createTestPageAndOpenParagraphModal();
  });

  it("Adds 'Text body' paragraph and verifies CKEditor content", () => {
    addParagraph("Text body");
    typeInCkEditor("Hello, world!");
    cy.saveContent();
    cy.get(".rich-text").should("contain", "Hello, world!");
  });

  it("Adds 'Media(s)' paragraph with a single image", () => {
    addParagraph("Media(s)");
    mediaLibrarySelect("paige-cody");
    cy.saveContent();
    checkImageSrc({
      selector: ".medias.medias--single img",
      expectedInSrc: "paige-cody",
    });
  });

  it("Adds 'Media(s)' paragraph with 2 images", () => {
    addParagraph("Media(s)");
    const images = ["paige-cody", "robert-collins"];
    images.forEach((img, index) => {
      mediaLibrarySelect(img, index);
    });
    cy.saveContent();
    checkImageSrc({
      selector: ".medias__item.medias__item--first img",
      expectedInSrc: "paige-cody",
    });
    checkImageSrc({
      selector: ".medias__item.medias__item--last img",
      expectedInSrc: "robert-collins-tvc5imO5pXk-unsplash_0.jpg",
    });
  });

  it("Adds multiple paragraphs: 'Media(s)' and 'Text body'", () => {
    addParagraph("Media(s)");
    mediaLibrarySelect("paige-cody");
    addAnotherParagraph();
    addParagraph("Text body");
    typeInCkEditor("Hello, world!");
    cy.saveContent();
    checkImageSrc({
      selector: ".medias.medias--single img",
      expectedInSrc: "paige-cody",
    });
    cy.get(".rich-text").should("contain", "Hello, world!");
  });

  it("Adds 'Simple links' paragraph with a single link", () => {
    const link = {
      url: "https://www.google.com/",
      text: "Google",
      targetBlank: false,
    };
    addParagraph("Simple links");
    addSimpleLink({ link });
    cy.saveContent();
    verifySimpleLink({ link });
  });

  it("Adds 'Simple links' paragraph with an external link", () => {
    const link = {
      url: "https://www.google.com/",
      text: "Google",
      targetBlank: true,
    };
    addParagraph("Simple links");
    addSimpleLink({ link });
    cy.saveContent();
    verifySimpleLink({ link });
  });

  it("Adds 'Simple links' paragraph with multiple links", () => {
    const links = [
      { url: "https://www.google.com/", text: "Google", targetBlank: false },
      { url: "https://www.reload.dk/", text: "Reload", targetBlank: true },
    ];
    addParagraph("Simple links");
    links.forEach((link, index) => addSimpleLink({ link, index }));
    cy.saveContent();
    links.forEach((link, index) => verifySimpleLink({ link, index }));
  });

  it("Adds 'Accordion' paragraph and verifies content toggle", () => {
    const accordionContent = {
      title: "Accordion title",
      content: "Accordion content",
    };
    addParagraph("Accordion");
    cy.findByLabelText("Accordion title").type(accordionContent.title);
    typeInCkEditor(accordionContent.content);
    cy.saveContent();
    cy.get("summary.disclosure__headline").should(
      "contain",
      accordionContent.title
    );
    cy.get("details.disclosure.disclosure--paragraph-width").should(
      "not.have.attr",
      "open"
    );
    cy.get("summary.disclosure__headline").click();
    cy.get("details.disclosure.disclosure--paragraph-width").should(
      "contain",
      accordionContent.content
    );
    cy.get("details.disclosure.disclosure--paragraph-width").should(
      "have.attr",
      "open"
    );
  });
});
