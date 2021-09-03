<?php
namespace Drupal\ddb_react_demo_mk6\Controller;
use Drupal\Core\Controller\ControllerBase;

class DddbReactDemoController extends ControllerBase
{
    public function index()
    {
        $checklist_data = [
            'material-list-url' => ding_react_material_list_url(),
            'cover-service-url' => ding_react_cover_service_url(),
            // We cannot use url() here as it will encode the colon in the placeholder.
            'material-url' => '/ting/object/:pid',
            'author-url' => '/search/ting/phrase.creator=":author"',
            'remove-button-text' => 'Remove from list',
            'empty-list-text' => 'List is empty.',
            'error-text' => 'An error occurred while trying to fetch list.',
            'of-text' => 'by'
          ];

        return [
            'checklist' => ding_react_app('checklist', $checklist_data),
            'button' => ding_react_ding_entity_buttons(),
        ];
    }
}