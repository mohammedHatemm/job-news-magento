<?php

namespace News\Manger\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;

class Form extends Generic
{
  protected $_systemStore;
  protected $_categoryCollection;

  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Data\FormFactory $formFactory,
    \Magento\Store\Model\System\Store $systemStore,
    \News\Manger\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
    array $data = []
  ) {
    $this->_systemStore = $systemStore;
    $this->_categoryCollection = $categoryCollectionFactory->create();
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
      $model = $this->_objectManager->create(\News\Manger\Model\Category::class);
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

    // إضافة حقول التاريخ مع التحقق من وجود البيانات
    if ($model->getId()) {
      $fieldset->addField('created_at', 'date', [
        'name' => 'created_at',
        'label' => __('Created At'),
        'title' => __('Created At'),
        'disabled' => true,
        'readonly' => true,
        'format' => 'yyyy-MM-dd HH:mm:ss',
        'date_format' => 'yyyy-MM-dd',
        'time_format' => 'HH:mm:ss'
      ]);

      $fieldset->addField('updated_at', 'date', [
        'name' => 'updated_at',
        'label' => __('Updated At'),
        'title' => __('Updated At'),
        'disabled' => true,
        'readonly' => true,
        'format' => 'yyyy-MM-dd HH:mm:ss',
        'date_format' => 'yyyy-MM-dd',
        'time_format' => 'HH:mm:ss'
      ]);
    }

    // إضافة حقل Parent Category مع التحقق من البيانات
    try {
      $parentOptions = $this->getCategoryOptions();

      $fieldset->addField('category_parent', 'select', [
        'name' => 'category_parent',
        'label' => __('Parent Category'),
        'title' => __('Parent Category'),
        'required' => false,
        'values' => $parentOptions
      ]);
    } catch (\Exception $e) {
      // في حالة فشل تحميل الفئات، إضافة حقل نصي بدلاً من select
      $fieldset->addField('category_parent', 'text', [
        'name' => 'category_parent',
        'label' => __('Parent Category ID'),
        'title' => __('Parent Category ID'),
        'required' => false,
        'note' => __('Enter parent category ID or leave empty for root category')
      ]);
    }

    // تحديد قيم النموذج مع التحقق من البيانات
    $formData = $model->getData();

    // التأكد من صحة البيانات قبل تحديدها
    if (is_array($formData)) {
      // تنظيف البيانات التي قد تسبب مشاكل في التنسيق
      if (isset($formData['created_at']) && empty($formData['created_at'])) {
        unset($formData['created_at']);
      }
      if (isset($formData['updated_at']) && empty($formData['updated_at'])) {
        unset($formData['updated_at']);
      }

      $form->setValues($formData);
    }

    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
   * الحصول على خيارات الفئات الأب
   *
   * @return array
   */
  protected function getCategoryOptions()
  {
    $options = [
      ['value' => '', 'label' => __('No Parent (Root Category)')]
    ];

    try {
      $collection = $this->_categoryCollection->load();

      foreach ($collection as $category) {
        $options[] = [
          'value' => $category->getId(),
          'label' => $category->getCategoryName() ?: __('Category #%1', $category->getId())
        ];
      }
    } catch (\Exception $e) {
      // في حالة الخطأ، إرجاع الخيار الافتراضي فقط
      $this->_logger->error('Error loading category options: ' . $e->getMessage());
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

    if ($this->getRequest()->getParam('back')) {
      $this->getLayout()->getBlock('page.title')->setPageTitle(__('Edit Category'));
    } else {
      $this->getLayout()->getBlock('page.title')->setPageTitle(__('New Category'));
    }

    return $this;
  }
}
