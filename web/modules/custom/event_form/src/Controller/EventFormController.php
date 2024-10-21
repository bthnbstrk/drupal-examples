<?php

namespace Drupal\event_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Provides route responses for the Example module.
 */
class EventFormController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function fetchAll()
  {

    $conn = Database::getConnection();
    $query = $conn->select('event_form', 'e')
      ->fields('e');

    $participants = $query->execute()->fetchAllAssoc('id');

    foreach ($participants as $participant)
    {

      $termId = $participant->event_type;
      $term = Term::load($termId);

      if ($term && $term->bundle() === 'event_type') {
        $participant->event_name = $term->getName();
      } else {
        $participant->event_name = 'Unknown';
      }

    }

    return [
      '#title' => 'Participants',
      '#theme' => 'event_form_template',
      '#items' => $participants
    ];

  }

  public function configList()
  {

    $conn = Database::getConnection();
    $query = $conn->select('event_form', 'e')
      ->fields('e');

    $participants = $query->execute()->fetchAllAssoc('id');

    foreach ($participants as $participant)
    {
      $termId = $participant->event_type;
      $term = Term::load($termId);

      if ($term && $term->bundle() === 'event_type') {
        $participant->event_name = $term->getName();
      } else {
        $participant->event_name = 'Unknown';
      }
    }

    return [
      '#title' => 'Participation Status Edit Page',
      '#theme' => 'event_config_template',
      '#items' => $participants
    ];

  }

  public function updateList(Request $request) {

    if ($request->isMethod('POST')) {

      $data = $request->request->all();

      foreach ($data['participants'] as $item) {

        $connection = Database::getConnection();

        $connection->update('event_form')
          ->fields(['participation_status' => $item['participant_status']])
          ->condition('id', $item['participant_id'])
          ->execute();
      }

      $this->messenger()->addMessage($this->t('Participants have been updated.'));

      return new Response('', 302, ['Location' => '/admin/event-form']);
    }

    return [
      '#markup' => $this->t('Invalid request.'),
    ];
  }
}
