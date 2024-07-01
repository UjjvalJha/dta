<?php

namespace Drupal\book_manager\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a confirmation form for deleting a book node.
 */
class BookDeleteForm extends ConfirmFormBase {

  /**
   * The book node to delete.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %title?', ['%title' => $this->node->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('book_manager.book_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    if (!$node || $node->getType() !== 'book') {
        throw new NotFoundHttpException();
    }
    $this->node = $node;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->node->delete();

    // Display a message that the node has been deleted.
    $this->messenger()->addStatus($this->t('The book %title has been deleted.', ['%title' => $this->node->label()]));

    // Redirect back to the book list.
    $form_state->setRedirect('book_manager.book_list');
  }

}
