<?php

declare(strict_types=1);

namespace Drupal\event_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Database;
/**
 * Provides a event_form form.
 */
final class EventForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'event_form_event';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $vid = 'event_type'; // Burada kendi vocabulary ID'nizi kullanın.
    $terms = Term::loadMultiple(\Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vid)
      ->accessCheck(TRUE) // Erişim kontrolü etkin
      ->execute());

    $options = [];

    foreach ($terms as $term) {
      $options[$term->id()] = $term->getName();
    }

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#required' => TRUE,
    ];

    $form['phone_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
    ];

    $form['birth_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Birth Date'),
      '#required' => TRUE,
    ];

    $form['event_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Type'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['participation_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Yes, i will be attend.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    $formField = $form_state->getValues();


    $firstName = trim($formField['first_name']);
    $lastName = trim($formField['last_name']);
    $email = trim($formField['email']);
    $phoneNumber = trim($formField['phone_number']);

    if (!preg_match("/^([a-zA-Z']+)$/", $firstName)) {
      $form_state->setErrorByName('emp_firstname', $this->t('Enter the valid first name'));
    }

    if (!preg_match("/^([a-zA-Z']+)$/", $lastName)) {
      $form_state->setErrorByName('emp_lastname', $this->t('Enter the valid last name'));
    }

    if (preg_match("/^([a-zA-Z']+)$/", $phoneNumber) && strlen($phoneNumber) != 11) {
      $form_state->setErrorByName('emp_lastname', $this->t('Enter the valid phone number'));
    }

    if (!\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Enter valid email address'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $conn = Database::getConnection();
    $formField = $form_state->getValues();

    $formData['first_name'] = $formField['first_name'];
    $formData['last_name'] = $formField['last_name'];
    $formData['email'] = $formField['email'];
    $formData['phone_number'] = $formField['phone_number'];
    $formData['event_type'] = $formField['event_type'];
    $formData['participation_status'] = $formField['participation_status'];
    $formData['birth_date'] = $formField['birth_date'];

    $conn->insert('event_form')->fields($formData)->execute();

    $this->messenger()->addStatus($this->t('Form data has been saved successfully.'));
    $form_state->setRedirect('<front>');
  }

}
