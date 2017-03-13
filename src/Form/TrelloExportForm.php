<?php

/**
 * @file
 * Contains \Drupal\trello\Form\TrelloExportForm.
 */

namespace Drupal\trello\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\trello\TrelloService;

/**
 * Class TrelloExportForm.
 *
 * @package Drupal\trello\Form
 */
class TrelloExportForm extends FormBase {

  /**
   * @var TrelloService $trello_service
   */
  protected $trello_service;

  /**
   * Class constructor.
   */
  public function __construct(TrelloService $trello_service) {
    $this->trello_service = $trello_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trello.trello_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trello_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $board = $form_state->getValue('board');
    $selected = isset($board) ? $board : '';

    $form['board'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Board'),
      '#options' => $this->getBoardOptions(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => array($this, 'boardCallback'),
        'wrapper' => 'dropdown-second-replace',
      ],
    ];
    $form['list'] = [
      '#type' => 'select',
      '#title' => $this->t('Select List'),
      '#options' => $this->getListOptions($selected),
      '#required' => TRUE,
      '#prefix' => '<div id="dropdown-second-replace">',
      '#suffix' => '</div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Retrieve Cards'),

      // Code from http://www.sitepoint.com/using-ajax-forms-drupal-8/
      '#ajax' => [
        'callback' => array($this, 'getResult'),
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Getting results...'),
        ),
      ],
      '#suffix' => '<div id="trello-export-result"></div>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Get list of open boards accessible to the current member.
   */
  public function getBoardOptions() {
    $data = [];
    $path = 'members/me/boards';
    $extra = [
      'filter' => 'open',
      'fields' => 'id,name',
    ];
    if ($response = $this->trello_service->request($path, $extra)) {
      foreach ($response as $value) {
        $data[$value->id] = $value->name;
      }
    }
    return $data;
  }

  /**
   * Get list of open lists on the specified board.
   */
  public function getListOptions($board) {
    if (empty($board)) {
      return [];
    }
    $data = [];
    $path = 'board/' . $board . '/lists' ;
    $extra = [
      'cards' => 'open',
      'fields' => 'id,name',
    ];
    if ($response = $this->trello_service->request($path, $extra)) {
      foreach ($response as $value) {
        $data[$value->id] =  $value->name;
      }
    }
    return $data;
  }

  /**
   * Ajax callback to create an HTML snippet for the selected values.
   */
  public function boardCallback(array &$form, FormStateInterface $form_state) {
    return $form['list'];
  }

  /**
   * Ajax callback to create an HTML snippet for the selected values.
   */
  public function getResult(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();

    /**
     * Get the specified fields for all open cards on this list.
     */
    $list = $form_state->getValue('list');
    $path = 'list/' . $list . '/cards';
    $extra = [
      'filter' => 'open',
      'members' => 'true',
      'member_fields' => 'fullName',
      'actions' => 'commentCard',
      'attachments' => 'true',
      'attachment_fieldss' => 'name,url',
      'checklists' => 'all',
    ];
    $cards = $this->trello_service->request($path, $extra);

    $build = [
      '#theme' => 'trello_export_theme',
      '#cards' => $cards,
    ];

    $response->addCommand(new HtmlCommand('#trello-export-result', $build));
    return $response;
  }
}
