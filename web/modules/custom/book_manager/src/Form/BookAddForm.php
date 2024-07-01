<?php

namespace Drupal\book_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Declare class book add form.
 */
class BookAddForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new BookAddForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];

    $form['author'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Author'),
      '#required' => TRUE,
    ];

    $form['publication_year'] = [
      '#type' => 'number',
      '#title' => $this->t('Publication Year'),
      '#required' => FALSE,
      '#default_value' => date('Y'),
      '#min' => 1900,
      '#max' => date('Y'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Process submitted data here, e.g., save to database.
    $title = $form_state->getValue('title');
    $author = $form_state->getValue('author');
    $publication_year = $form_state->getValue('publication_year');

    // Get the node storage.
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    // Create a new book node.
    $node = $nodeStorage->create([
      'type' => 'book',
      'title' => $title,
      'field_author' => $author,
      'field_publication_year' => $publication_year,
      'status' => 1,
    ]);

    // Save the node.
    $node->save();

    // Display a message.
    $this->messenger->addMessage($this->t('Book "%title" has been created.', ['%title' => $title]));

    // Redirect to book list page.
    $form_state->setRedirect('book_manager.book_list');
  }

}
