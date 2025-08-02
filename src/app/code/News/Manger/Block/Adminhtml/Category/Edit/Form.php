<?php

namespace News\Manger\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Form extends Generic
{
  /**
   * @var \Magento\Store\Model\System\Store
   */
  protected $_systemStore;

  /**
   * @var \News\Manger\Model\ResourceModel\Category\CollectionFactory
   */
  protected $_categoryCollectionFactory;

  /**
   * @var \News\Manger\Model\CategoryFactory
   */
  protected $_categoryFactory;

  /**
   * @var LoggerInterface
   */
  protected $_logger;

  /**
   * @param \Magento\Backend\Block\Template\Context $context
   * @param \Magento\Framework\Registry $registry
   * @param \Magento\Framework\Data\FormFactory $formFactory
   * @param \Magento\Store\Model\System\Store $systemStore
   * @param \News\Manger\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
   * @param \News\Manger\Model\CategoryFactory $categoryFactory
   * @param LoggerInterface $logger
   * @param array $data
   */
  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Data\FormFactory $formFactory,
    \Magento\Store\Model\System\Store $systemStore,
    \News\Manger\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
    \News\Manger\Model\CategoryFactory $categoryFactory,
    LoggerInterface $logger,
    array $data = []
  ) {
    $this->_systemStore = $systemStore;
    $this->_categoryCollectionFactory = $categoryCollectionFactory;
    $this->_categoryFactory = $categoryFactory;
    $this->_logger = $logger;
    parent::__construct($context, $registry, $formFactory, $data);
  }

  /**
   * Init form
   */
  protected function _construct()
  {
    parent::_construct();
    $this->setId('category_form');
    $this->setTitle(__('Category Information'));
  }

  /**
   * Prepare form
   *
   * @return $this
   * @throws LocalizedException
   */
  protected function _prepareForm()
  {
    $model = $this->_coreRegistry->registry('news_category');

    if (!$model) {
      $model = $this->_categoryFactory->create();
    }

    $form = $this->_formFactory->create([
      'data' => [
        'id' => 'edit_form',
        'action' => $this->getData('action'),
        'method' => 'post',
        'enctype' => 'multipart/form-data'
      ]
    ]);

    $form->setHtmlIdPrefix('category_');

    $fieldset = $form->addFieldset('base_fieldset', [
      'legend' => __('General Information'),
      'class' => 'fieldset-wide'
    ]);

    if ($model->getId()) {
      $fieldset->addField('category_id', 'hidden', [
        'name' => 'category_id'
      ]);
    }

    $fieldset->addField('category_name', 'text', [
      'name' => 'category_name',
      'label' => __('Category Name'),
      'title' => __('Category Name'),
      'required' => true,
      'class' => 'required-entry'
    ]);

    $fieldset->addField('category_description', 'textarea', [
      'name' => 'category_description',
      'label' => __('Category Description'),
      'title' => __('Category Description'),
      'required' => true,
      'class' => 'required-entry'
    ]);

    $fieldset->addField('category_status', 'select', [
      'name' => 'category_status',
      'label' => __('Category Status'),
      'title' => __('Category Status'),
      'required' => true,
      'class' => 'required-entry',
      'values' => [
        ['value' => 1, 'label' => __('Active')],
        ['value' => 0, 'label' => __('Inactive')]
      ]
    ]);

    try {
      $parentOptions = $this->getBreadcrumbCategoryOptions($model->getId());

      $fieldset->addField('parent_ids', 'checkboxes', [
        'name' => 'parent_ids[]',
        'label' => __('Parent Categories'),
        'title' => __('Parent Categories'),
        'required' => false,
        'values' => $parentOptions,
        'note' => __('Select one or more parent categories to define hierarchy.')
      ]);
    } catch (\Exception $e) {
      $this->_logger->error('Error loading category options: ' . $e->getMessage());
      // Optionally add a disabled field with an error message
      $fieldset->addField('parent_ids', 'note', [
        'label' => __('Parent Categories'),
        'text' => __('Could not load category options. Please check logs.')
      ]);
    }

    $formData = $model->getData();

    // Ensure parent_ids is an array for the form
    if (isset($formData['parent_ids'])) {
      $decoded = is_string($formData['parent_ids']) ? json_decode($formData['parent_ids'], true) : $formData['parent_ids'];
      $formData['parent_ids'] = is_array($decoded) ? $decoded : [];
    }

    $form->setValues($formData);
    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
   * Get category options with breadcrumb path.
   * This is the main entry point for generating the options.
   *
   * @param int|null $excludeId
   * @return array
   */
  protected function getBreadcrumbCategoryOptions($excludeId = null): array
  {
    $collection = $this->_categoryCollectionFactory->create()->addOrder('category_name', 'ASC');
    return $this->buildHierarchyOptions($collection, $excludeId);
  }

  /**
   * Builds a hierarchical options array from a category collection.
   * This improved method builds a complete parent-child map first,
   * then recursively generates the options.
   *
   * @param \News\Manger\Model\ResourceModel\Category\Collection $collection
   * @param int|null $excludeId
   * @return array
   */
  protected function buildHierarchyOptions($collection, $excludeId = null): array
  {
    $options = [];
    $categoryMap = [];
    $childrenMap = [];

    // First, map all categories by their ID and build a children map
    foreach ($collection as $category) {
      if ($excludeId && $category->getId() == $excludeId) {
        continue; // Skip the category being edited
      }
      $categoryId = $category->getId();
      $parentIds = json_decode($category->getData('parent_ids') ?: '[]', true);

      $categoryMap[$categoryId] = $category;

      if (empty($parentIds)) {
        $childrenMap[0][] = $categoryId; // 0 is the virtual root
      } else {
        foreach ($parentIds as $parentId) {
          $childrenMap[$parentId][] = $categoryId;
        }
      }
    }

    // Now, build the options recursively starting from the virtual root
    if (isset($childrenMap[0])) {
      foreach ($childrenMap[0] as $rootCategoryId) {
        if (isset($categoryMap[$rootCategoryId])) {
          $this->addCategoryToOptions(
            $categoryMap[$rootCategoryId],
            $categoryMap,
            $childrenMap,
            $options
          );
        }
      }
    }

    return $options;
  }

  /**
   * A recursive helper function to build the final options array.
   *
   * @param \News\Manger\Model\Category $category
   * @param array $categoryMap
   * @param array $childrenMap
   * @param array &$options
   * @param string $breadcrumbPath
   */
  protected function addCategoryToOptions($category, array $categoryMap, array $childrenMap, array &$options, string $breadcrumbPath = '')
  {
    $currentPath = $breadcrumbPath ? ($breadcrumbPath . ' > ') : '';
    $currentPath .= $category->getCategoryName() ?: __('Category #%1', $category->getId());

    $options[] = [
      'value' => $category->getId(),
      'label' => $currentPath,
    ];

    $categoryId = $category->getId();
    if (isset($childrenMap[$categoryId])) {
      foreach ($childrenMap[$categoryId] as $childId) {
        if (isset($categoryMap[$childId])) {
          $this->addCategoryToOptions(
            $categoryMap[$childId],
            $categoryMap,
            $childrenMap,
            $options,
            $currentPath
          );
        }
      }
    }
  }
}
