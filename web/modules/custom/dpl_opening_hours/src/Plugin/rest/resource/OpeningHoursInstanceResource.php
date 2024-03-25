<?php

declare(strict_types = 1);

namespace Drupal\dpl_opening_hours\Plugin\rest\resource;

use Drupal\Component\Utility\NestedArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Manage individual opening hours.
 *
 * @RestResource (
 *   id = "dpl_opening_hours",
 *   label = @Translation("Manage individual opening hours"),
 *   uri_paths = {
 *     "canonical" = "/dpl_opening_hours/{id}",
 *     "create" = "/dpl_opening_hours",
 *   },
 * )
 */
final class OpeningHoursInstanceResource extends OpeningHoursResourceBase {

  /**
   * {@inheritDoc}
   */
  public function getPluginDefinition(): array {
    return NestedArray::mergeDeep(
      parent::getPluginDefinition(),
      [
        'payload' => [
          'name' => 'opening_hours_instance',
          'description' => 'The opening hours instance to manage',
          'in' => 'body',
          'required' => FALSE,
          'schema' => $this->openingHoursInstanceSchema(require_id: FALSE),
        ],
        'responses' => [
          Response::HTTP_OK => [
            'description' => Response::$statusTexts[Response::HTTP_OK],
            'schema' => $this->openingHoursInstanceSchema(),
          ],
          Response::HTTP_NO_CONTENT => [
            'description' => Response::$statusTexts[Response::HTTP_NO_CONTENT],
          ],
          Response::HTTP_BAD_REQUEST => [
            'description' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
          ],
          Response::HTTP_INTERNAL_SERVER_ERROR => [
            'description' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
          ],
        ],
      ]
    );
  }

  /**
   * Retrieve individual opening hours.
   */
  public function get(int $id, Request $request): Response {
    $instance = $this->repository->load($id);
    if (!$instance) {
      throw new NotFoundHttpException("No instance for id: '{$id}");
    }
    $reponseDate = $this->mapper->toResponse($instance);
    return new Response($this->serializer->serialize($reponseDate, $this->serializerFormat($request)));
  }

  /**
   * Create new opening hours instances.
   */
  public function post(Request $request): Response {
    try {
      $requestData = $this->deserialize($request);
      $instance = $this->mapper->fromRequest($requestData);
      $this->repository->upsert($instance);

      $responseData = $this->mapper->toResponse($instance);
      return new Response($this->serializer->serialize($responseData, $this->serializerFormat($request)));
    }
    catch (\InvalidArgumentException $e) {
      throw new BadRequestHttpException("Invalid input: {$e->getMessage()}");
    }
  }

  /**
   * Update an opening hours instance.
   */
  public function patch(int $id, Request $request): Response {
    try {
      $requestData = $this->deserialize($request);
      if ($id !== $requestData->getId()) {
        throw new \InvalidArgumentException("Instance ids provided in path '{$id}' and body '{$requestData->getId()}' do not match ");
      }
      $instance = $this->repository->load($id);
      if (!$instance) {
        throw new NotFoundHttpException("Invalid instance id: '{$id}'");
      }

      $updatedInstance = $this->mapper->fromRequest($requestData);
      $this->repository->upsert($updatedInstance);

      $responseData = $this->mapper->toResponse($updatedInstance);
      return new Response($this->serializer->serialize($responseData, $this->serializerFormat($request)));
    }
    catch (\InvalidArgumentException $e) {
      throw new BadRequestHttpException('Invalid input: ' . $e->getMessage());
    }
  }

  /**
   * Delete an opening hours instance.
   */
  public function delete(int $id): Response {
    $deleted = $this->repository->delete($id);
    if (!$deleted) {
      throw new BadRequestHttpException("No instance for id '{$id}'");
    }

    return new Response("", Response::HTTP_NO_CONTENT);
  }

}