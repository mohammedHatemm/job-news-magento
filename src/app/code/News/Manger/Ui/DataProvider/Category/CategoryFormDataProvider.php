<?php

namespace News\Manger\Ui\DataProvider\Category;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\Category\CollectionFactory;
use News\Manger\Model\CategoryFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class CategoryFormDataProvider extends AbstractDataProvider
{
  protected $collection;
  protected $dataPersistor;
  protected $categoryFactory;
  protected $request;
  protected $logger;
  protected $loadedData;

  /**
   * @param string $name
   * @param string $primaryFieldName
   * @param string $requestFieldName
   * @param CollectionFactory $collectionFactory
   * @param CategoryFactory $categoryFactory
   * @param DataPersistorInterface $dataPersistor
   * @param RequestInterface $request
   * @param LoggerInterface $logger
   * @param array $meta
   * @param array $data
   */
  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    CollectionFactory $collectionFactory,
    CategoryFactory $categoryFactory,
    DataPersistorInterface $dataPersistor,
    RequestInterface $request,
    LoggerInterface $logger,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collectionFactory->create();
    $this->dataPersistor = $dataPersistor;
    $this->categoryFactory = $categoryFactory;
    $this->request = $request;
    $this->logger = $logger;
    parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
  }

  /**
   * Get data for form
   *
   * @return array
   */
  public function getData()
  {
    if (isset($this->loadedData)) {
      return $this->loadedData;
    }

    $items = $this->collection->getItems();
    foreach ($items as $category) {
      $this->loadedData[$category->getId()] = $category->getData();
    }

    // Get data from data persistor if available
    $data = $this->dataPersistor->get('news_category');
    if (!empty($data)) {
      $category = $this->collection->getNewEmptyItem();
      $category->setData($data);
      $this->loadedData[$category->getId()] = $category->getData();
      $this->dataPersistor->clear('news_category');
    }

    return $this->loadedData;
  }

  /**
   * Get meta information for form
   *
   * @return array
   */
  public function getMeta()
  {
    $meta = parent::getMeta();

    // Get current category ID for exclusion in parent dropdown
    $categoryId = $this->request->getParam('category_id');

    // Get categories for parent dropdown
    $categoryModel = $this->categoryFactory->create();
    $parentOptions = $categoryModel->getCategoriesForDropdown($categoryId, true, 'No Parent (Root Category)');

    // Add parent dropdown options to meta
    $meta['general']['children']['parent_id']['arguments']['data']['config']['options'] = $parentOptions;

    // Add validation rules
    $meta['general']['children']['parent_id']['arguments']['data']['config']['validation'] = [
      'validate-select' => true
    ];

    // Add category level info if editing
    if ($categoryId) {
      $currentCategory = $this->categoryFactory->create()->load($categoryId);
      if ($currentCategory->getId()) {
        $meta['general']['children']['category_info'] = [
          'arguments' => [
            'data' => [
              'config' => [
                'componentType' => 'container',
                'component' => 'Magento_Ui/js/form/components/html',
                'content' => $this->getCategoryInfoHtml($currentCategory),
                'label' => __('Category Information'),
                'sortOrder' => 5
              ]
            ]
          ]
        ];
      }
    }

    // Add depth validation
    $maxDepth = $categoryModel->getMaxAllowedDepth();
    $meta['general']['children']['parent_id']['arguments']['data']['config']['notice'] =
      __('Maximum category depth allowed: %1', $maxDepth);

    return $meta;
  }

  /**
   * Get category information HTML
   *
   * @param \News\Manger\Model\Category $category
   * @return string
   */
  protected function getCategoryInfoHtml($category)
  {
    $html = '<div class="category-info">';
    $html .= '<p><strong>' . __('Current Level:') . '</strong> ' . $category->getLevel() . '</p>';
    $html .= '<p><strong>' . __('Full Path:') . '</strong> ' . $category->getPath() . '</p>';
    // $html .= '<p><strong>' . __('Children Count:') . '</strong> ' . $category->getChildrenCount() . '</p>';

    if ($category->hasChildren()) {
      $html .= '<p><strong>' . __('Has Children:') . '</strong> ' . __('Yes') . '</p>';
      $html .= '<div class="category-children">';
      $html .= '<strong>' . __('Children:') . '</strong>';
      $html .= '<ul>';

      $children = $category->getChildrenCategories(true);
      foreach ($children as $child) {
        $html .= '<li>' . $child->getCategoryName() . '</li>';
      }
      $html .= '</ul>';
      $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
  }

  /**
   * Get categories tree for JavaScript
   *
   * @return array
   */
  public function getCategoriesTree()
  {
    $categoryModel = $this->categoryFactory->create();
    return $categoryModel->getCategoryTreeForJs(true);
  }

  /**
   * Get available parent categories
   *
   * @param int|null $excludeId
   * @return array
   */
  public function getAvailableParents($excludeId = null)
  {
    $categoryModel = $this->categoryFactory->create();
    return $categoryModel->getCategoriesForDropdown($excludeId, true);
  }

  /**
   * Validate category data before save
   *
   * @param array $data
   * @return array
   */
  public function validateCategoryData($data)
  {
    $errors = [];

    if (!empty($data['parent_id'])) {
      $categoryModel = $this->categoryFactory->create();

      // Load parent category
      $parentCategory = $this->categoryFactory->create()->load($data['parent_id']);

      if (!$parentCategory->getId()) {
        $errors[] = __('Selected parent category does not exist.');
      } else {
        // Check depth limit
        $maxDepth = $categoryModel->getMaxAllowedDepth();
        if ($parentCategory->getLevel() >= ($maxDepth - 1)) {
          $errors[] = __('Cannot create category. Maximum depth limit (%1) would be exceeded.', $maxDepth);
        }
      }
    }

    return $errors;
  }
}
