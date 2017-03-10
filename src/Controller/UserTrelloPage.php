<?php

/**
 * @file
 * Contains \Drupal\trello\Controller\UserTrelloPage.
 */
namespace Drupal\trello\Controller;

use Drupal\Core\Controller\ControllerBase;

class UserTrelloPage extends ControllerBase {
  public function trelloExportPage($user) {
    $build = [];

    $form = \Drupal::formBuilder()->getForm('Drupal\trello\Form\TrelloExportForm');
    $build['trello_export_block'][] = $form;
    $build['#cache'] = ['max-age' => 0];

    return $build;
  }
}
