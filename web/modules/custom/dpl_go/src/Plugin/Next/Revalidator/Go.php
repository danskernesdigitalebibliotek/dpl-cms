<?php

namespace Drupal\dpl_go\Plugin\Next\Revalidator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\next\Event\EntityActionEvent;
use Drupal\next\Plugin\ConfigurableRevalidatorBase;
use Drupal\next\Plugin\RevalidatorInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a revalidator for GO paths.
 *
 * @Revalidator(
 *  id = "go",
 *  label = "Go",
 *  description = "Path and tag based on-demand revalidation."
 * )
 */
class Go extends ConfigurableRevalidatorBase implements RevalidatorInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'revalidate_page' => NULL,
      'additional_paths' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['revalidate_page'] = [
      '#title' => $this->t('Revalidate page'),
      '#description' => $this->t('Revalidate the page for the entity on update.'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['revalidate_page'],
    ];

    $form['additional_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional paths'),
      '#default_value' => $this->configuration['additional_paths'],
      '#description' => $this->t('Additional paths to revalidate on entity update. Enter one path per line. Example %example.', [
        '%example' => '/blog',
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['revalidate_page'] = $form_state->getValue('revalidate_page');
    $this->configuration['additional_paths'] = $form_state->getValue('additional_paths');
  }

  /**
   * {@inheritdoc}
   */
  public function revalidate(EntityActionEvent $event): bool {
    $revalidated = FALSE;

    $sites = $event->getSites();
    if (!count($sites)) {
      return FALSE;
    }

    $paths = [];
    if (!empty($this->configuration['revalidate_page'])) {
      $paths[] = $event->getEntityUrl();
    }
    if (!empty($this->configuration['additional_paths'])) {
      $paths = array_merge($paths, array_map('trim', explode("\n", $this->configuration['additional_paths'])));
    }

    if (!count($paths)) {
      return FALSE;
    }

    foreach ($paths as $path) {
      foreach ($sites as $site) {
        try {
          $revalidate_url = $site->getRevalidateUrl();

          if (!$revalidate_url) {
            throw new \Exception('No revalidate url set.');
          }

          if ($this->nextSettingsManager->isDebug()) {
            $this->logger->notice('(@action): Revalidating path %path for the site %site. URL: %url', [
              '@action' => $event->getAction(),
              '%path' => $path,
              '%site' => $site->label(),
              '%url' => $revalidate_url,
            ]);
          }
          $entity = $event->getEntity();
          $response = $this->httpClient->request('POST', $revalidate_url, [
            'headers' => [
              'Authorization' => 'Bearer dGVzdDp0ZXN0',
              'Content-Type' => 'application/json',
              'User-Agent' => 'insomnia/10.1.1',
            ],
            'json' => [
              'type' => 'path',
              'paths' => [$path],
              'contentType' => $entity->getEntityTypeId() === 'node' ? $entity->bundle() : $entity->getEntityTypeId(),
            ],
          ]);
          if ($response && $response->getStatusCode() === Response::HTTP_OK) {
            if ($this->nextSettingsManager->isDebug()) {
              $this->logger->notice('(@action): Successfully revalidated path %path for the site %site. URL: %url', [
                '@action' => $event->getAction(),
                '%path' => $path,
                '%site' => $site->label(),
                '%url' => $revalidate_url,
              ]);
            }

            $revalidated = TRUE;
          }
        }
        catch (\Exception $exception) {
          $this->logger->error($exception->getMessage());
          $revalidated = FALSE;
        }
      }
    }

    return $revalidated;
  }

}
