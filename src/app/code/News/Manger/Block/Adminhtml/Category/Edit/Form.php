<?php

namespace News\Manger\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Form extends Generic
{
  protected $_systemStore;
  protected $_categoryCollection;
  protected $_categoryFactory;
  protected $_logger;

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
    $this->_categoryCollection = $categoryCollectionFactory->create();
    $this->_categoryFactory = $categoryFactory;
    $this->_logger = $logger;
    parent::__construct($context, $registry, $formFactory, $data);
  }

  protected function _construct()
  {
    parent::_construct();
    $this->setId('category_form');
    $this->setTitle(__('Category Information'));
  }

  protected function _prepareForm()
  {
    $model = $this->_coreRegistry->registry('news_category');

    // التأكد من وجود الـ model
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
        ['value' => '', 'label' => __('Please Select')],
        ['value' => 1, 'label' => __('Active')],
        ['value' => 0, 'label' => __('Inactive')]
      ]
    ]);

    // إضافة حقل Parent Category مع التحقق من البيانات
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
      // في حالة فشل تحميل الفئات، إضافة حقل نصي بدلاً من select
      $fieldset->addField('parent_id', 'text', [
        'name' => 'parent_id',
        'label' => __('Parent Category ID'),
        'title' => __('Parent Category ID'),
        'required' => false,
        'note' => __('Enter parent category ID or leave empty for root category')
      ]);
    }

    // إضافة حقول التاريخ للعرض فقط



    // تحديد قيم النموذج مع التحقق من البيانات
    $formData = $model->getData();

    // التأكد من صحة البيانات قبل تحديدها
    if (is_array($formData) && !empty($formData)) {
      $form->setValues($formData);
    }

    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
   * الحصول على خيارات الفئات الأب
   *
   * @param int|null $excludeId
   * @return array
   */
  protected function getCategoryOptions($excludeId = null)
  {
    $options = [
      ['value' => '', 'label' => __('No Parent (Root Category)')]
    ];

    try {
      $collection = $this->_categoryCollection->load();

      foreach ($collection as $category) {
        // استبعاد الفئة الحالية لمنع الحلقة المفرغة
        if ($excludeId && $category->getId() == $excludeId) {
          continue;
        }

        $options[] = [
          'value' => $category->getId(),
          'label' => $category->getCategoryName() ?: __('Category #%1', $category->getId())
        ];
      }
    } catch (\Exception $e) {
      $this->_logger->error('Error loading category options: ' . $e->getMessage());
      throw $e;
    }

    return $options;
  }

  /**
   * تحضير التسميات للحقول
   *
   * @return $this
   */
  protected function _prepareLayout()
  {
    parent::_prepareLayout();

    $pageTitle = $this->getLayout()->getBlock('page.title');
    if ($pageTitle) {
      if ($this->getRequest()->getParam('category_id')) {
        $pageTitle->setPageTitle(__('Edit Category'));
      } else {
        $pageTitle->setPageTitle(__('New Category'));
      }
    }

    return $this;
  }
}
