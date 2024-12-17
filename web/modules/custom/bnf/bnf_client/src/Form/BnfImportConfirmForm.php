<?php

namespace Drupal\bnf_client\Form;

use Drupal\bnf\Services\BnfImporter;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displaying an import preview, and allowing editor to import.
 */
class BnfImportConfirmForm implements FormInterface, ContainerInjectionInterface {
  use StringTranslationTrait;

  use AutowireTrait;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    protected RouteMatchInterface $routeMatch,
    protected BnfImporter $bnfImporter,
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('current_route_match'),
      $container->get('bnf.importer')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'bnf_import_form_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#title'] = $this->t('Confirm import of BNF content', [], ['context' => 'BNF']);

    $uuid = $this->routeMatch->getParameter('uuid');
    $bnfServer = (string) getenv('BNF_SERVER_GRAPHQL_ENDPOINT');

    $form_state->set('uuid', $uuid);
    $form_state->set('bnfServer', $bnfServer);

    $nodeData = $this->bnfImporter->loadNodeData($uuid, $bnfServer);

    $form['uuid'] = [
      '#title' => 'UUID',
      '#type' => 'textfield',
      '#default_value' => $uuid,
      '#disabled' => TRUE,
    ];

    $form['label'] = [
      '#title' => $this->t('Content label', [], ['context' => 'BNF']),
      '#type' => 'textfield',
      '#default_value' => $nodeData['title'] ?? NULL,
      '#disabled' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import content'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $uuid = $form_state->get('uuid');
    $bnfServer = $form_state->get('bnfServer');
    $node = $this->bnfImporter->importNode($uuid, $bnfServer);
    $form_state->setRedirect('entity.node.edit_form', ['node' => $node->id()]);
  }

}
