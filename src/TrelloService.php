<?php

/**
 * @file
 * Contains \Drupal\trello\TrelloService.
 */

namespace Drupal\trello;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityManagerInterface;

class TrelloService {

  /**
   * @var AccountInterface $current_user
   */
  protected $current_user;
  protected $entity_manager;
  protected $base_path;
  protected $trello_token;
  protected $trello_key;

  /**
   * When the service is created, set variables.
   */
  public function __construct(AccountInterface $current_user, EntityManagerInterface $entity_manager) {

    $this->current_user = $current_user;
    $this->entity_manager = $entity_manager;
    $this->setValue('base_path', 'https://api.trello.com/1');

    // Set the key and token of the current user, if available.
    $user_storage = $this->entity_manager->getStorage('user');
    $user = $user_storage->load($this->current_user->id());
    $key = $user->get('field_trello_key')->value;
    $token = $user->get('field_trello_token')->value;
    if (!empty($key) && !empty($token)) {
      $this->setValue('key', $key);
      $this->setValue('token', $token);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity.manager')
    );
  }

  /**
   * Set the value of a variable.
   */
  public function setValue($arg, $value) {
    switch ($arg) {
      case 'base_path':
        $this->base_path = $value;
        break;
      case 'token':
        $this->trello_token = $value;
        break;
      case 'key':
        $this->trello_key = $value;
        break;
    }
  }

  /**
   * Return the value of a variable.
   */
  public function getValue($arg) {
    switch ($arg) {
      case 'base_path':
        return $this->base_path;
      case 'token':
        return $this->trello_token;
      case 'key':
        return $this->trello_key;
    }
  }

  /**
   * Make a Trello request.
   */
  public function request($path, $query_extra = []) {

    $client = \Drupal::httpClient();
    $query['query'] = [
      'key' => $this->getValue('key'),
      'token' => $this->getValue('token'),
    ] + $query_extra;
    $path = $this->getValue('base_path') . '/' . $path;
    $path = Url::fromUri($path, $query)->toString();
    try {
      $request = $client->get($path);
      $response = json_decode($request->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('trello', $e->getMessage());
      $response = [];
    }
    return $response;

  }

}
