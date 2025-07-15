<?php

namespace News\Manger\Block\Adminhtml\News\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use News\Manger\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Form extends Generic
{
  /**
   * @var CategoryCollectionFactory
   */
  protected $categoryCollectionFactory;

  /**
   * Form constructor.
   */
  public function __construct(
    Context $context,
    Registry $registry,
    FormFactory $formFactory,
    CategoryCollectionFactory $categoryCollectionFactory
  ) {
    $this->categoryCollectionFactory = $categoryCollectionFactory;
    parent::__construct($context, $registry, $formFactory);
  }

  /**
   * Prepare form
   */
  protected function _prepareForm()
  {
    /** @var \News\Manger\Model\News $model */
    $model = $this->_coreRegistry->registry('news_news');

    /** @var \Magento\Framework\Data\Form $form */
    $form = $this->_formFactory->create([
      'data' => [
        'id' => 'edit_form',
        'action' => $this->getData('action'),
        'method' => 'post'
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



    // عرض قائمة أسماء التصنيفات
    try {
      $parentOptions = $this->getCategoryOptions($model->getId());

      $fieldset->addField('parent_id', 'select', [
        'name' => 'parent_id',
        'label' => __('Parent Category'),
        'title' => __('Parent Category'),
        'required' => false,
        'values' => $parentOptions
      ]);
    } catch (\Exception $e) {
      $this->_logger->error('Error loading category options: ' . $e->getMessage());
      $fieldset->addField('parent_id', 'text', [
        'name' => 'parent_id',
        'label' => __('Parent Category ID'),
        'title' => __('Parent Category ID'),
        'required' => false,
        'note' => __('Enter parent category ID or leave empty')
      ]);
    }

    $form->setValues($model->getData());
    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
   * Load available categories to populate the select field
   */
  protected function getCategoryOptions($currentId = null)
  {
    $collection = $this->categoryCollectionFactory->create();
    $collection->addFieldToSelect(['category_id', 'category_name']);

    $collection->addFieldToFilter('category_status', ['eq' => 1]);

    if ($currentId) {
      $collection->addFieldToFilter('category_id', ['neq' => $currentId]);
    }

    $options = [['value' => '', 'label' => __('-- Please Select --')]];

    foreach ($collection as $category) {
      $options[] = [
        'value' => $category->getCategoryId(),
        'label' => $category->getCategoryName()
      ];
    }

    return $options;
  }
}
