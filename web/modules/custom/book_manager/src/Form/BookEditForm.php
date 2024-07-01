<?php

namespace Drupal\book_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Form controller for the book edit form.
 */
class BookEditForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new BookEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $current_user, RequestStack $request_stack, MessengerInterface $messenger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->requestStack->getCurrentRequest()->attributes->get('node');
    if (!$node || $node->getType() !== 'book') {
      throw new NotFoundHttpException();
    }

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $node->getTitle(),
      '#required' => TRUE,
    ];

    $form['author'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Author'),
      '#default_value' => $node->get('field_author')->value,
      '#required' => TRUE,
    ];

    $form['publication_year'] = [
      '#type' => 'number',
      '#title' => $this->t('Publication Year'),
      '#default_value' => $node->get('field_publication_year')->value,
      '#required' => FALSE,
      '#min' => 1900,
      '#max' => date('Y'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->requestStack->getCurrentRequest()->attributes->get('node');

    // Update node values.
    $node->setTitle($form_state->getValue('title'));
    $node->set('field_author', $form_state->getValue('author'));
    $node->set('field_publication_year', $form_state->getValue('publication_year'));

    // Save the updated node.
    $node->save();

    // Display a message.
    $this->messenger->addMessage($this->t('The book %title has been updated.', ['%title' => $node->getTitle()]));

    // Redirect to book list page.
    $form_state->setRedirect('book_manager.book_list');
  }

}
