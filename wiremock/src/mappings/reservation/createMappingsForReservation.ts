import { Options } from "wiremock-rest-client/dist/model/options.model";
import wiremock from "../../lib/general";

export default (baseUri?: string, options?: Options) => {

  // Get searchFacets.
  import("./data/fbi/patron.json").then((json) => {
    wiremock(baseUri, options).mappings.createMapping({
      request: {
        urlPattern: "/external/agencyid/patrons/patronid/v2",
      },
      response: {
        jsonBody: json,
      },
    });
  });

};
