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

    // إضافة حقل Parent Category مع Breadcrumb Style
    try {
      $parentOptions = $this->getBreadcrumbCategoryOptions($model->getId());

      $fieldset->addField('parent_id', 'select', [
        'name' => 'parent_id',
        'label' => __('Parent Category'),
        'title' => __('Parent Category'),
        'required' => false,
        'values' => $parentOptions,
        'note' => __('Select a parent category to create hierarchy. You can choose any level.')
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
   * الحصول على خيارات الفئات بنمط Breadcrumb
   *
   * @param int|null $excludeId
   * @return array
   */
  protected function getBreadcrumbCategoryOptions($excludeId = null)
  {
    $options = [
      ['value' => '', 'label' => __('No Parent (Root Category)')]
    ];

    try {
      // جلب كل الفئات
      $collection = $this->_categoryCollection->load();

      // تحويل الفئات إلى array مع معرف الفئة كمفتاح
      $categoriesById = [];
      foreach ($collection as $category) {
        $categoriesById[$category->getId()] = $category;
      }

      // بناء الهيكل الهرمي
      $hierarchyOptions = $this->buildHierarchyOptions($categoriesById, $excludeId);

      // دمج الخيارات
      $options = array_merge($options, $hierarchyOptions);
    } catch (\Exception $e) {
      $this->_logger->error('Error loading breadcrumb category options: ' . $e->getMessage());
      throw $e;
    }

    return $options;
  }

  /**
   * بناء خيارات الهيكل الهرمي
   *
   * @param array $categoriesById
   * @param int|null $excludeId
   * @return array
   */
  protected function buildHierarchyOptions($categoriesById, $excludeId = null)
  {
    $options = [];

    // العثور على الفئات الجذرية (بدون والد)
    $rootCategories = [];
    foreach ($categoriesById as $category) {
      if (!$category->getParentId()) {
        $rootCategories[] = $category;
      }
    }

    // ترتيب الفئات الجذرية حسب الاسم
    usort($rootCategories, function ($a, $b) {
      return strcmp($a->getCategoryName(), $b->getCategoryName());
    });

    // بناء الهيكل الهرمي لكل فئة جذرية
    foreach ($rootCategories as $rootCategory) {
      $this->addCategoryToOptions($rootCategory, $categoriesById, $options, $excludeId);
    }

    return $options;
  }

  /**
   * إضافة فئة وأطفالها إلى الخيارات
   *
   * @param \News\Manger\Model\Category $category
   * @param array $categoriesById
   * @param array &$options
   * @param int|null $excludeId
   * @param string $breadcrumbPath
   */
  protected function addCategoryToOptions($category, $categoriesById, &$options, $excludeId = null, $breadcrumbPath = '')
  {
    // استبعاد الفئة الحالية لمنع الحلقة المفرغة
    if ($excludeId && $category->getId() == $excludeId) {
      return;
    }

    // بناء مسار breadcrumb
    $currentPath = $breadcrumbPath;
    if ($currentPath) {
      $currentPath .= ' > ';
    }
    $currentPath .= $category->getCategoryName() ?: __('Category #%1', $category->getId());

    // إضافة الفئة الحالية إلى الخيارات
    $options[] = [
      'value' => $category->getId(),
      'label' => $currentPath
    ];

    // العثور على الفئات الفرعية
    $children = [];
    foreach ($categoriesById as $childCategory) {
      if ($childCategory->getParentId() == $category->getId()) {
        $children[] = $childCategory;
      }
    }

    // ترتيب الفئات الفرعية حسب الاسم
    usort($children, function ($a, $b) {
      return strcmp($a->getCategoryName(), $b->getCategoryName());
    });

    // إضافة الفئات الفرعية بشكل تكراري
    foreach ($children as $child) {
      $this->addCategoryToOptions($child, $categoriesById, $options, $excludeId, $currentPath);
    }
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
