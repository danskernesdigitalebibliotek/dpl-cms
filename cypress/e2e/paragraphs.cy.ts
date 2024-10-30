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

// INSPRIATION: https://github.com/kanopi/shrubs/blob/main/mediaLibrarySelect.js
const mediaLibrarySelect = (fileName: string, id: number) => {
  // Need to create unique intercepts for each media library select
  const mediaNodeEditAjax = `mediaNodeEditAjax${id}`;
  const mediaLibraryAjax = `mediaLibraryAjax${id}`;
  const viewsAjax = `viewsAjax${id}`;

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

  it("Can add 'media'", () => {
    addParagraph("Media(s)");

    mediaLibrarySelect("Læseklubber", 1);

    cy.saveContent();

    checkImageSrc({
      selector: ".medias.medias--single img",
      expectedInSrc: "laeseklubber.jpg",
    });
  });

  it("Can add 'media' (2 images)", () => {
    addParagraph("Media(s)");
    const images = ["Læseklubber", "robert-collins"];

    images.forEach((img, index) => {
      mediaLibrarySelect(img, index);
    });

    cy.saveContent();

    checkImageSrc({
      selector: ".medias__item.medias__item--first img",
      expectedInSrc: "laeseklubber.jpg",
    });

    checkImageSrc({
      selector: ".medias__item.medias__item--last img",
      expectedInSrc: "robert-collins-tvc5imO5pXk-unsplash_0.jpg",
    });
  });

  it("Can add 'Simple link'", () => {
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

  it("Can add 'Simple link (Externt)'", () => {
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

  it("Can add 'Simple links (2)'", () => {
    const links = [
      { url: "https://www.google.com/", text: "Google", targetBlank: false },
      { url: "https://www.reload.dk/", text: "Reload", targetBlank: true },
    ];

    addParagraph("Simple links");
    links.forEach((link, index) => addSimpleLink({ link, index }));
    cy.saveContent();
    links.forEach((link, index) => verifySimpleLink({ link, index }));
  });

  it("Can add 'Accordion'", () => {
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
