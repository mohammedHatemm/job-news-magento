<?php

namespace News\Manger\Block\Adminhtml\News\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use News\Manger\Model\CategoryFactory;
use Psr\Log\LoggerInterface;

class Form extends Generic
{
  protected $categoryFactory;
  protected $logger;

  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Data\FormFactory $formFactory,
    CategoryFactory $categoryFactory,
    LoggerInterface $logger,
    array $data = []
  ) {
    $this->categoryFactory = $categoryFactory;
    $this->logger = $logger;
    parent::__construct($context, $registry, $formFactory, $data);
  }

  protected function _prepareForm()
  {
    /** @var \News\Manger\Model\News $model */
    $model = $this->_coreRegistry->registry('news_news');

    /** @var \Magento\Framework\Data\Form $form */
    $form = $this->_formFactory->create([
      'data' => [
        'id' => 'edit_form',
        'action' => $this->getData('action'),
        'method' => 'post',
        'enctype' => 'multipart/form-data'
      ]
    ]);

    $form->setHtmlIdPrefix('news_');

    $fieldset = $form->addFieldset(
      'base_fieldset',
      ['legend' => __('General Information'), 'class' => 'fieldset-wide']
    );

    if ($model->getId()) {
      $fieldset->addField('news_id', 'hidden', ['name' => 'news_id']);
    }

    $fieldset->addField(
      'news_title',
      'text',
      [
        'name' => 'news_title',
        'label' => __('Title'),
        'title' => __('Title'),
        'required' => true
      ]
    );

    $fieldset->addField(
      'news_content',
      'textarea',
      [
        'name' => 'news_content',
        'label' => __('Content'),
        'title' => __('Content'),
        'required' => true
      ]
    );

    $fieldset->addField(
      'news_status',
      'select',
      [
        'name' => 'news_status',
        'label' => __('Status'),
        'title' => __('Status'),
        'required' => true,
        'options' => [
          1 => __('Active'),
          0 => __('Inactive')
        ]
      ]
    );

    try {
      $categoryOptions = $this->getCategoryOptions($model->getId());

      $fieldset->addField(
        'category_ids',
        'multiselect',
        [
          'name' => 'category_ids[]',
          'label' => __('Categories'),
          'title' => __('Categories'),
          'required' => false,
          'values' => $categoryOptions,
          'note' => __('Select one or more categories for this news.')
        ]
      );
    } catch (\Exception $e) {
      $this->logger->error('Error loading category options: ' . $e->getMessage());
      $fieldset->addField('category_ids', 'note', [
        'label' => __('Categories'),
        'text' => __('Could not load category options. Please check logs.')
      ]);
    }

    $formData = $model->getData();

    // Load existing category IDs for the news
    if ($model->getId()) {
      $categoryIds = $this->getCategoryIdsForNews($model->getId());
      $formData['category_ids'] = $categoryIds;
    } else {
      $formData['category_ids'] = [];
    }

    $form->setValues($formData);
    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  protected function getCategoryOptions($excludeId = null): array
  {
    $collection = $this->categoryFactory->create()->getCollection()->addOrder('category_name', 'ASC');
    $options = [];
    $categoryMap = [];
    $childrenMap = [];

    // Build category hierarchy
    foreach ($collection as $category) {
      if ($excludeId && $category->getId() == $excludeId) {
        continue;
      }
      $categoryId = $category->getId();
      $parentIds = json_decode($category->getData('parent_ids') ?: '[]', true);

      $categoryMap[$categoryId] = $category;

      if (empty($parentIds)) {
        $childrenMap[0][] = $categoryId;
      } else {
        foreach ($parentIds as $parentId) {
          $childrenMap[$parentId][] = $categoryId;
        }
      }
    }

    // Generate hierarchical options
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

  protected function getCategoryIdsForNews($newsId)
  {
    try {
      $connection = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
      $select = $connection->select()
        ->from($connection->getTableName('news_news_category'), ['category_id'])
        ->where('news_id = ?', $newsId);
      return $connection->fetchCol($select);
    } catch (\Exception $e) {
      $this->logger->error('Error getting category IDs for news: ' . $e->getMessage());
      return [];
    }
  }
}
