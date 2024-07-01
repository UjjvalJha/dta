<?php

namespace Drupal\book_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BookManagerController extends ControllerBase {

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
   * Constructs a new BookManagerController object.
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
   * Displays a paginated list of books.
   *
   * @return array
   *   Render array for book list table.
   */
  public function list(Request $request) {
    $header = [
      ['data' => $this->t('Title'), 'field' => 'title'],
      ['data' => $this->t('Author'), 'field' => 'field_author'],
      ['data' => $this->t('Publication Year'), 'field' => 'field_publication_year'],
      ['data' => $this->t('Operations')],
    ];

    // Get current page number.
    $current_page = $request->query->get('page', 1);

    // Define items per page.
    $items_per_page = 50;

    // Query to fetch nodes.
    $query = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'book')
      ->sort('created', 'DESC')
      ->pager($items_per_page)
      ->accessCheck(TRUE);

    // Get the paged list of node IDs.
    $nids = $query->pager($items_per_page)->execute();

    // Load nodes based on fetched IDs.
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $rows = [];
    foreach ($nodes as $node) {
      $row = [];
      $row[] = $node->toLink();
      $row[] = $node->field_author->value;
      $row[] = $node->field_publication_year->value;

      // Generate edit and delete links.
      $editUrl = Url::fromRoute('book_manager.book_edit', ['node' => $node->id()])->toString();
      $deleteUrl = Url::fromRoute('book_manager.book_delete', ['node' => $node->id()])->toString();

      $row[] = $this->t('<a href="@edit">Edit</a> | <a href="@delete">Delete</a>', [
        '@edit' => $editUrl,
        '@delete' => $deleteUrl,
      ]);
      $rows[] = $row;
    }

    // Build the table render array.
    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No books available.'),
      '#cache' =>['max-age' => 0]
    ];

    // Add pager to the render array.
    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }
}
