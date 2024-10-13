import * as dayjs from "dayjs";
import 'dayjs/locale/da'
import "cypress-if";

dayjs.locale('da')

// The fields of the eventseries entity, that we use when creating and comparing.
export interface EventType {
  uuid?: string,
  title?: string,
  subtitle: string,
  recurType: 'custom' | 'weekly_recurring_date',
  ticketManagerRelevance: boolean,
  state: string,
  start: dayjs,
  end: dayjs,
  status: boolean,
}

// This interface only has what we currently use - ideally, it could be
// expanded to follow the Swagger spec.
interface EventApiType {
  url: string,
  uuid: string,
  title: string,
  description: string,
  ticket_manager_relevance: boolean,
  state: string,
  date_time: {
    start: string,
    end: string,
  },
  external_data: {
    url: string,
    admin_url: string,
  }
}

export const eventBase: EventType = {
  // Creating a random title, that we can use as a makeshift ID.
  subtitle: Math.random().toString(36).slice(2, 7),
  // Generating a random boolean.
  ticketManagerRelevance: true,
  recurType: "custom",
  start: dayjs("2030-12-15T10:00:00"),
  end: dayjs("2031-02-15T16:00:00"),
  state: "Active",
  status: true,
};

// This function assumes we're already on the create-event-series page.
// This function is only pulled out, as the date part of the creation is fairly
// complex, due to Recurring_Events.
function setEventSeriesDate(event: EventType) {
  // The Drupal module is inconsistent with naming.
  const fieldKey = (event.recurType == 'custom') ? `${event.recurType}_date` : event.recurType;

  cy.get(`[name="${fieldKey}[0][value][date]"]`)
    .type(event.start.format("YYYY-MM-DD")).focus();

  cy.wait(1000);

  cy.get(`[name="${fieldKey}[0][end_value][date]"]`)
    // Checking that the end date is pre-filled, based on start date.
    .should("have.value", event.start.format("YYYY-MM-DD"))
    .type(event.end.format("YYYY-MM-DD"));

  if (event.recurType == 'custom') {
    cy.get(`[name="${fieldKey}[0][value][time]"]`)
      .type(event.start.format("HH:mm")).focus();

    cy.wait(500);

    cy.get(`[name="${fieldKey}[0][end_value][time]"]`)
      // Checking that the end time is pre-filled, based on start time.
      .should("have.value", event.start.add(1, "hour").format("HH:mm"))
      .type(event.end.format("HH:mm"));
  }
  else if (event.recurType == 'weekly_recurring_date') {
    // The non-custom display, has a different way of choosing times.
    const startTimeLabel = event.start.format("HH:mm");
    const endTimeLabel = event.end.format("HH:mm");

    cy.get('[name="weekly_recurring_date[0][time]"]').select(startTimeLabel)
    cy.contains('Set End Time').click();
    cy.get('[name="weekly_recurring_date[0][end_time][time]"]').select(endTimeLabel)

    // Clicking all the days off, so we can easier match the dates later.
    cy.get('[name^="weekly_recurring_date[0][days]["]').click({multiple: true});
  }
}

function createEventSeries(event: EventType) {
  // Recurring_event throws weird exceptions here, that we want to avoid failing
  // the whole cypress test.
  Cypress.on('uncaught:exception', () => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })

  cy.drupalLogin("/events/add/default");

  cy.findByLabelText("Title").type(event.title);
  cy.findByLabelText("Subtitle").type(event.subtitle);

  cy.findByLabelText("State").select(event.state, {
    // We have to use force when using Select2.
    force: true,
  });

  if (!event.ticketManagerRelevance) {
    cy.contains('Show sidebar panel').click();
    cy.findByLabelText("Relevant for ticket manager").click();
    cy.contains('Close sidebar panel').click();
  }

  if (!event.status) {
    cy.get('[data-drupal-selector="edit-status-value"]').click();
  }

  cy.findByLabelText("Recur Type").select(event.recurType, {
    // We have to use force when using Select2.
    force: true,
  });

  setEventSeriesDate(event);

  cy.findByRole("button", { name: "Save" }).click();
}

function deleteEventSeries(event: EventType) {
  cy.drupalLogin("/admin/content/eventseries");

  cy.contains(event.title)
    .parents("tr")
    .find("td li.dropbutton-toggle button")
    .click()
    .then(($button) => {
      cy.wrap($button)
        .parent(".dropbutton-toggle")
        .parent("ul.dropbutton")
        .find("li.delete a")
        .click();
      cy.get(".ui-dialog .form-submit")
        .filter(":visible")
        .should("exist")
        .click();
    });
}

function findEventsInAPI(event: EventType) {
  // Make an API call to get the list of events
  return cy.request("/api/v1/events").then((response) => {
    expect(response.status).to.eq(200);

    const events = response.body as EventApiType[];
    const matchingEvents = events.filter((apiEvent: any) => apiEvent.title === event.title);

    if (matchingEvents.length) {
      // Let's check that the interface values has been set.
      const apiEvent = matchingEvents[0];

      expect(event.title).to.eq(apiEvent.title);
      expect(event.ticketManagerRelevance).to.eq(apiEvent.ticket_manager_relevance);
      expect(event.start.format("YYYY-MM-DDTHH:mm:ssZ")).to.eq(apiEvent.date_time.start);

      if (event.recurType == 'custom') {
        expect(event.end.format("YYYY-MM-DDTHH:mm:ssZ")).to.eq(apiEvent.date_time.end);
      } else {
        // If it is a reccurring, non-custom event, the end date will be the
        // same as the start, but the time will be that of the end date.
        const date = event.start.format("YYYY-MM-DD") + event.end.format("THH:mm:ssZ");
        expect(date).to.eq(apiEvent.date_time.end);
      }
    }

    return cy.wrap(matchingEvents);
  });
}

function visitEventEditLink() {
  cy.get('.event-list-stacked a').first().click().then(() => {
    cy.document().then((doc) => {
      const eventUrl = doc.querySelector('link[rel="shortlink"]').getAttribute('href');
      const editLink = `${eventUrl}/edit`;

      // Now use Cypress to visit the edit link
      cy.visit(editLink);
    });
  });
}

describe("Events API", () => {
  it("Series with single instance", () => {
    const event = {...eventBase};
    event.title = Math.random().toString(36).slice(2, 7);

    createEventSeries(event)

    findEventsInAPI(event).then((events) => {
      expect(events.length).to.eq(1, `Expected exactly one eventinstance, but found ${events.length}`);
    });

    deleteEventSeries(event);
  });

  it("Unpublished events should not show up in API", () => {
    const event = {...eventBase};
    event.status = false;
    event.title = Math.random().toString(36).slice(2, 7);
    createEventSeries(event)

    findEventsInAPI(event).then((events) => {
      expect(events.length).to.eq(0, `Expected no events, due to unpublished, but found ${events.length}`);
    });

    deleteEventSeries(event);
  });

  it("Series with many instances", () => {
    const event = {...eventBase};
    event.title = Math.random().toString(36).slice(2, 7);
    event.recurType = "weekly_recurring_date";

    createEventSeries(event);

    findEventsInAPI(event).then((events) => {
      expect(events.length).to.greaterThan(1, `Expected to find multiple events in series, but found ${events.length}`);
      return events[0];
    });

    // Let's edit some details, that we can later look up in the API.
    visitEventEditLink();

    const newTitle = Math.random().toString(36).slice(2, 7);
    cy.findByLabelText("Title").type(newTitle);
    cy.findByRole("button", { name: "Save" }).click();

    const newEvent = {...event};
    newEvent.title = newTitle;

    findEventsInAPI(newEvent).then((events) => {
      expect(events.length).to.eq(1, `Expected exactly one eventinstance after editing, but found ${events.length}`);
    });

    // Unpublishing the same instance.
    cy.get('.breadcrumb').contains(event.title).click();
    visitEventEditLink();

    cy.get('[id="edit-status-value"]').click();
    cy.findByRole("button", { name: "Save" }).click();

    findEventsInAPI(newEvent).then((events) => {
      expect(events.length).to.eq(0, `Expected unpublished event to not show up in API`);
    });

    deleteEventSeries(event);
  });

  it("Updating an event using API", () => {
    const event = {...eventBase};
    event.title = Math.random().toString(36).slice(2, 7);
    createEventSeries(event);

    const patchBody = {
      state: 'SoldOut',
      external_data: {
        url: "https://event.local",
        admin_url: "https://admin.local",
      }
    }

    findEventsInAPI(event).then((events) => {
      const apiEvent = events[0];
      // Resetting the logged-in session, for calling API.
      cy.clearCookies();
      cy.clearAllSessionStorage();

      const extUsername = 'external_system';
      const extPassword = Cypress.env("CYPRESS_DRUPAL_PASSWORD");

      cy.request({
        method: 'PATCH',
        url: `/api/v1/events/${apiEvent.uuid}`,
        auth: {
          user: extUsername,
          pass: extPassword,
        },
        body: patchBody
      }).then((response) => {
        expect(response.status).to.eq(200);
      });
    });

    findEventsInAPI(event).then((events) => {
      expect(events.length).to.eq(1, `Expected to find the PATCH'ed event using title ${event.title}`);
      const apiEvent = events[0];
      expect(apiEvent.state).to.eq(patchBody.state, `Expected updated event state to be ${patchBody.state}, but found ${apiEvent.state}`);
      expect(apiEvent.external_data.url).to.eq(patchBody.external_data.url, `Expected updated event external url to be ${patchBody.external_data.url}, but found ${apiEvent.external_data.url}`);
      expect(apiEvent.external_data.admin_url).to.eq(patchBody.external_data.admin_url, `Expected updated event external admin url to be ${patchBody.external_data.admin_url}, but found ${apiEvent.external_data.admin_url}`);
    });

    deleteEventSeries(event);
  });
});
