import { WorkPage } from '../../pages/work-page';

describe("Work page", () => {
  it('Has the title of the work', () => {
    const work = new WorkPage();

    work.visit(['work-of:870970-basis:25245784']);
    cy.getBySel('material-header-content')
      .scrollIntoView()
      .contains('Harry Potter og Fønixordenen');
    //cy.wait(2000);

    work.elements.page_title().should('eq', 'Harry Potter og Fønixordenen | DPL CMS');
  });
});
