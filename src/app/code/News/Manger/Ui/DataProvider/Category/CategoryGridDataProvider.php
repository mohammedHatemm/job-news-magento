<?php

namespace News\Manger\Ui\DataProvider\Category;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Api\Search\SearchResultInterface;
use Psr\Log\LoggerInterface;
use News\Manger\Model\Category;
use News\Manger\Model\CategoryFactory;

class CategoryGridDataProvider extends AbstractDataProvider
{
  protected $loadedData;
  private $logger;
  private $categoryFactory;
  private $categoryModel;

  /**
   * @param string $name
   * @param string $primaryFieldName
   * @param string $requestFieldName
   * @param SearchResultInterface $collection
   * @param LoggerInterface $logger
   * @param CategoryFactory $categoryFactory
   * @param array $meta
   * @param array $data
   */
  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    SearchResultInterface $collection,
    LoggerInterface $logger,
    CategoryFactory $categoryFactory,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collection;
    $this->logger = $logger;
    $this->categoryFactory = $categoryFactory;
    $this->categoryModel = $categoryFactory->create();
    parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
  }

  public function getData()
  {
    if (isset($this->loadedData)) {
      return $this->loadedData;
    }

    try {
      // Log the collection class to make sure we're using the right one
      $this->logger->debug('Collection Class: ' . get_class($this->collection));

      // Log the SQL query
      $this->logger->debug('Collection SQL: ' . $this->collection->getSelect()->__toString());

      $items = $this->collection->getItems();
      $this->logger->debug('Collection Size: ' . $this->collection->getSize());

      $formattedItems = [];
      foreach ($items as $item) {
        $data = $item->getData();

        // Load full category model to use hierarchy functions
        $categoryModel = $this->categoryFactory->create()->load($item->getId());

        // Transform category_status
        $data['category_status'] = $data['category_status'] == 1 ? __('Enabled') : __('Disabled');

        // Use hierarchy functions for better display
        $data['formatted_name'] = $categoryModel->getFormattedName('├── ');
        $data['level'] = $categoryModel->getLevel();
        $data['full_path'] = $categoryModel->getPath(' > ');
        $data['is_root'] = $categoryModel->isRoot() ? __('Yes') : __('No');
        $data['children_count'] = $categoryModel->getChildrenCount();
        $data['has_children'] = $categoryModel->hasChildren() ? __('Yes') : __('No');

        // Ensure parent_name exists
        if (!isset($data['parent_name']) || $data['parent_name'] === null) {
          $data['parent_name'] = __('No Parent');
        }

        // Add breadcrumb path for better navigation
        $breadcrumbs = $categoryModel->getBreadcrumbPath();
        $data['breadcrumb_names'] = array_column($breadcrumbs, 'name');

        $formattedItems[] = $data;
      }

      $this->loadedData = [
        'totalRecords' => $this->collection->getSize(),
        'items' => $formattedItems
      ];

      $this->logger->debug('Final LoadedData: ' . json_encode($this->loadedData));
    } catch (\Exception $e) {
      $this->logger->error('CategoryGridDataProvider Error: ' . $e->getMessage());
      $this->logger->error('Stack Trace: ' . $e->getTraceAsString());
      $this->loadedData = [
        'totalRecords' => 0,
        'items' => []
      ];
    }

    return $this->loadedData;
  }

  /**
   * Get data for specific fields
   *
   * @return array
   */
  public function getMeta()
  {
    $meta = parent::getMeta();

    // Add custom meta for hierarchy display
    $meta['news_category_columns']['children']['formatted_name']['arguments']['data']['config']['visible'] = true;
    $meta['news_category_columns']['children']['level']['arguments']['data']['config']['visible'] = true;
    $meta['news_category_columns']['children']['full_path']['arguments']['data']['config']['visible'] = true;
    $meta['news_category_columns']['children']['children_count']['arguments']['data']['config']['visible'] = true;
    $meta['news_category_columns']['children']['parent_name']['arguments']['data']['config']['visible'] = true;

    return $meta;
  }

  /**
   * Get categories for dropdown (used in forms)
   *
   * @param int|null $excludeId
   * @return array
   */
  public function getCategoriesForDropdown($excludeId = null)
  {
    return $this->categoryModel->getCategoriesForDropdown($excludeId, true, 'No Parent (Root Category)');
  }

  /**
   * Get category tree for JavaScript components
   *
   * @return array
   */
  public function getCategoryTreeForJs()
  {
    return $this->categoryModel->getCategoryTreeForJs(true);
  }

  /**
   * Get category statistics
   *
   * @return array
   */
  public function getCategoryStats()
  {
    $stats = [];
    $items = $this->collection->getItems();

    foreach ($items as $item) {
      $categoryModel = $this->categoryFactory->create()->load($item->getId());
      $stats[] = $categoryModel->getCategoryStats();
    }

    return $stats;
  }

  /**
   * Get root categories for quick access
   *
   * @return array
   */
  public function getRootCategoriesData()
  {
    $rootCategories = $this->categoryModel->getRootCategories(true);
    $rootData = [];

    foreach ($rootCategories as $root) {
      $rootData[] = [
        'id' => $root->getId(),
        'name' => $root->getCategoryName(),
        'children_count' => $root->getChildrenCount(),
        'tree' => $root->getChildrenTree()
      ];
    }

    return $rootData;
  }
}
