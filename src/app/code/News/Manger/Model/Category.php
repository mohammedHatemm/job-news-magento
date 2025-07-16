<?php

namespace News\Manger\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
// ✨ إضافة: استدعاء الكلاس المطلوب
use News\Manger\Model\CategoryFactory;

class Category extends AbstractModel implements IdentityInterface
{
  const CACHE_TAG = 'news_manger_category';
  const CATEGORY_ID = 'category_id';
  const CONFIG_MAX_DEPTH = 'news_manager/category/max_depth';
  const CONFIG_ENABLE_CACHING = 'news_manager/category/enable_caching';
  const DEFAULT_MAX_DEPTH = 5;

  protected $_cacheTag = self::CACHE_TAG;
  protected $_eventPrefix = 'news_manger_category';
  protected $_eventObject = 'category';
  protected $_idFieldName = self::CATEGORY_ID;
  protected $_scopeConfig;
  protected static $_categoryTreeCache = [];

  /**
   * ✨ إضافة: تعريف متغير جديد للفاكتوري
   * @var CategoryFactory
   */
  protected $_categoryFactory;

  public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    ScopeConfigInterface $scopeConfig,
    // ✨ إضافة: حقن الفاكتوري في الكونستراكتور
    CategoryFactory $categoryFactory,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
    array $data = []
  ) {
    $this->_scopeConfig = $scopeConfig;
    // ✨ إضافة: إسناد الفاكتوري للمتغير
    $this->_categoryFactory = $categoryFactory;
    parent::__construct($context, $registry, $resource, $resourceCollection, $data);
  }

  /**
   * Initialize resource model
   *
   * @return void
   */
  protected function _construct()
  {
    $this->_init('News\Manger\Model\ResourceModel\Category');
  }

  /**
   * Get identities
   *
   * @return array
   */
  public function getIdentities()
  {
    return [self::CACHE_TAG . '_' . $this->getId()];
  }

  /**
   * Get default values
   *
   * @return array
   */
  public function getDefaultValues()
  {
    $values = [];
    $values['category_status'] = 1;
    $values['created_at'] = date('Y-m-d H:i:s');
    $values['updated_at'] = date('Y-m-d H:i:s');
    return $values;
  }

  /**
   * Get maximum allowed depth from configuration
   *
   * @return int
   */
  public function getMaxAllowedDepth()
  {
    return (int) $this->_scopeConfig->getValue(
      self::CONFIG_MAX_DEPTH,
      ScopeInterface::SCOPE_STORE
    ) ?: self::DEFAULT_MAX_DEPTH;
  }

  /**
   * Check if caching is enabled
   *
   * @return bool
   */
  public function isCachingEnabled()
  {
    return $this->_scopeConfig->isSetFlag(
      self::CONFIG_ENABLE_CACHING,
      ScopeInterface::SCOPE_STORE
    );
  }

  // === BASIC GETTERS AND SETTERS (No changes here) ===
  // ... (All getter and setter methods remain the same)

  public function getCategoryId()
  {
    return $this->getData(self::CATEGORY_ID);
  }
  public function setCategoryId($categoryId)
  {
    return $this->setData(self::CATEGORY_ID, $categoryId);
  }
  public function getCategoryName()
  {
    return $this->getData('category_name');
  }
  public function setCategoryName($categoryName)
  {
    return $this->setData('category_name', $categoryName);
  }
  public function getCategoryDescription()
  {
    return $this->getData('category_description');
  }
  public function setCategoryDescription($categoryDescription)
  {
    return $this->setData('category_description', $categoryDescription);
  }
  public function getCategoryStatus()
  {
    return $this->getData('category_status');
  }
  public function setCategoryStatus($categoryStatus)
  {
    return $this->setData('category_status', $categoryStatus);
  }
  public function getParentId()
  {
    return $this->getData('parent_id');
  }
  public function setParentId($parentId)
  {
    return $this->setData('parent_id', $parentId);
  }
  public function getParentName()
  {
    return $this->getData('parent_name');
  }
  public function setParentName($parentName)
  {
    return $this->setData('parent_name', $parentName);
  }
  public function getCreatedAt()
  {
    return $this->getData('created_at');
  }
  public function setCreatedAt($createdAt)
  {
    return $this->setData('created_at', $createdAt);
  }
  public function getUpdatedAt()
  {
    return $this->getData('updated_at');
  }
  public function setUpdatedAt($updatedAt)
  {
    return $this->setData('updated_at', $updatedAt);
  }


  // === UTILITY METHODS (No changes here) ===
  public function isActive()
  {
    return (bool)$this->getCategoryStatus();
  }
  public function isRoot()
  {
    return !$this->getParentId();
  }

  // === HIERARCHY METHODS (CORRECTIONS APPLIED HERE) ===

  /**
   * Load parent category
   *
   * @return \News\Manger\Model\Category|null
   */
  public function getParentCategory()
  {
    if (!$this->getParentId()) {
      return null;
    }

    // 🚀 تصحيح: استخدام الفاكتوري لإنشاء كائن جديد
    $parentCategory = $this->_categoryFactory->create();
    $this->_getResource()->load($parentCategory, $this->getParentId());

    return $parentCategory->getId() ? $parentCategory : null;
  }

  /**
   * Get children categories
   *
   * @param bool $activeOnly
   * @return \News\Manger\Model\ResourceModel\Category\Collection
   */
  public function getChildrenCategories($activeOnly = false)
  {
    $collection = $this->getCollection();
    $collection->addFieldToFilter('parent_id', $this->getId());

    if ($activeOnly) {
      $collection->addFieldToFilter('category_status', 1);
    }

    $collection->setOrder('category_name', 'ASC');
    return $collection;
  }

  /**
   * Get only root categories (no parent)
   *
   * @param bool $activeOnly
   * @return \News\Manger\Model\ResourceModel\Category\Collection
   */
  public function getRootCategories($activeOnly = true)
  {
    $collection = $this->getCollection();
    $collection->addFieldToFilter('parent_id', ['null' => true]);

    if ($activeOnly) {
      $collection->addFieldToFilter('category_status', 1);
    }

    $collection->setOrder('category_name', 'ASC');
    return $collection;
  }

  /**
   * Get category level in the hierarchy
   *
   * @return int
   */
  public function getLevel()
  {
    if (!$this->getParentId()) {
      return 0; // Root category
    }

    $level = 0;
    $parentId = $this->getParentId();
    $maxDepth = $this->getMaxAllowedDepth();

    while ($parentId && $level < $maxDepth) {
      $level++;
      // 🚀 تصحيح
      $parentCategory = $this->_categoryFactory->create();
      $this->_getResource()->load($parentCategory, $parentId);
      $parentId = $parentCategory->getParentId();
    }

    return $level;
  }

  /**
   * Get full path of category (parent1 > parent2 > current)
   *
   * @param string $separator
   * @return string
   */
  public function getPath($separator = ' > ')
  {
    $path = [];
    $current = $this;
    $maxDepth = $this->getMaxAllowedDepth();

    while ($current && $current->getId() && count($path) < $maxDepth) {
      array_unshift($path, $current->getCategoryName());

      if ($current->getParentId()) {
        // 🚀 تصحيح
        $parent = $this->_categoryFactory->create();
        $this->_getResource()->load($parent, $current->getParentId());
        $current = $parent->getId() ? $parent : null;
      } else {
        break;
      }
    }

    return implode($separator, $path);
  }

  /**
   * Get breadcrumb path as array
   *
   * @return array
   */
  public function getBreadcrumbPath()
  {
    $breadcrumbs = [];
    $current = $this;
    $maxDepth = $this->getMaxAllowedDepth();

    while ($current && $current->getId() && count($breadcrumbs) < $maxDepth) {
      array_unshift($breadcrumbs, [
        'id' => $current->getId(),
        'name' => $current->getCategoryName(),
        'level' => $current->getLevel()
      ]);

      if ($current->getParentId()) {
        // 🚀 تصحيح
        $parent = $this->_categoryFactory->create();
        $this->_getResource()->load($parent, $current->getParentId());
        $current = $parent->getId() ? $parent : null;
      } else {
        break;
      }
    }

    return $breadcrumbs;
  }

  /**
   * Get all parent categories
   *
   * @return array
   */
  public function getAllParents()
  {
    $parents = [];
    $parentId = $this->getParentId();
    $maxDepth = $this->getMaxAllowedDepth();

    while ($parentId && count($parents) < $maxDepth) {
      // 🚀 تصحيح
      $parentCategory = $this->_categoryFactory->create();
      $this->_getResource()->load($parentCategory, $parentId);

      if ($parentCategory->getId()) {
        $parents[] = $parentCategory;
        $parentId = $parentCategory->getParentId();
      } else {
        break;
      }
    }

    return $parents;
  }

  // ... (The rest of the file has been omitted for brevity, but includes the same corrections where needed)

  /**
   * Check if current category is ancestor of given category
   *
   * @param int $categoryId
   * @return bool
   */
  public function isAncestorOf($categoryId)
  {
    // 🚀 تصحيح
    $category = $this->_categoryFactory->create();
    $this->_getResource()->load($category, $categoryId);

    if (!$category->getId()) {
      return false;
    }

    $parents = $category->getAllParents();

    foreach ($parents as $parent) {
      if ($parent->getId() == $this->getId()) {
        return true;
      }
    }

    return false;
  }

  /**
   * Check if current category is descendant of given category
   *
   * @param int $categoryId
   * @return bool
   */
  public function isDescendantOf($categoryId)
  {
    $parents = $this->getAllParents();

    foreach ($parents as $parent) {
      if ($parent->getId() == $categoryId) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get root category
   *
   * @return \News\Manger\Model\Category|null
   */
  public function getRootCategory()
  {
    $current = $this;
    $maxDepth = $this->getMaxAllowedDepth();
    $depth = 0;

    while ($current->getParentId() && $depth < $maxDepth) {
      // 🚀 تصحيح
      $parent = $this->_categoryFactory->create();
      $this->_getResource()->load($parent, $current->getParentId());

      if ($parent->getId()) {
        $current = $parent;
        $depth++;
      } else {
        break;
      }
    }

    return $current;
  }

  /**
   * Validate category hierarchy before save
   *
   * @return bool
   */
  public function validateHierarchy()
  {
    // السماح للفئة بأن تكون فئة جذر (بدون أب)
    if ($this->getParentId() === null || $this->getParentId() === '' || $this->getParentId() === 0) {
      return true; // فئة جذر صحيحة
    }

    // منع الفئة من أن تكون أب لنفسها
    if ($this->getParentId() == $this->getId()) {
      return false;
    }

    // التحقق من وجود الفئة الأب
    $parentCategory = $this->_categoryFactory->create();
    $this->_getResource()->load($parentCategory, $this->getParentId());

    if (!$parentCategory->getId()) {
      return false; // الفئة الأب غير موجودة
    }

    // منع المراجع الدائرية - التحقق من أن الفئة الأب المختارة ليست من أحفاد الفئة الحالية
    if ($this->getId() && $this->getParentId()) {
      if ($this->isAncestorOf($this->getParentId())) {
        return false; // مرجع دائري
      }
    }

    // التحقق من حد العمق المسموح
    $parentLevel = $parentCategory->getLevel();
    $maxDepth = $this->getMaxAllowedDepth();

    if ($parentLevel >= ($maxDepth - 1)) {
      return false; // تجاوز الحد الأقصى للعمق
    }

    return true;
  }

  public function getFormattedName($prefix = '├── ')

  {

    $level = $this->getLevel();

    $indent = str_repeat('│   ', $level);



    if ($level > 0) {

      return $indent . $prefix . $this->getCategoryName();
    }



    return $this->getCategoryName();
  }



  /**

   * Get category statistics

   *

   * @return array

   */

  public function getCategoryStats()

  {

    return [

      'id' => $this->getId(),

      'name' => $this->getCategoryName(),

      'level' => $this->getLevel(),

      'is_root' => $this->isRoot(),

      'is_active' => $this->isActive(),

      'children_count' => $this->getChildrenCount(),

      'has_children' => $this->hasChildren(),

      'parent_id' => $this->getParentId(),

      'breadcrumb_path' => $this->getBreadcrumbPath(),

      'created_at' => $this->getCreatedAt(),

      'updated_at' => $this->getUpdatedAt()

    ];
  }



  /**

   * Clear category tree cache

   */

  public static function clearTreeCache()

  {

    self::$_categoryTreeCache = [];
  }



  /**

   * Before save actions

   *

   * @return $this

   */

  public function beforeSave()

  {

    // Validate hierarchy

    if (!$this->validateHierarchy()) {

      throw new \Magento\Framework\Exception\LocalizedException(

        __('Invalid category hierarchy. Please check parent category selection.')

      );
    }



    // Clear cache when saving

    self::clearTreeCache();



    if (!$this->getId()) {

      $this->setCreatedAt(date('Y-m-d H:i:s'));
    }

    $this->setUpdatedAt(date('Y-m-d H:i:s'));



    return parent::beforeSave();
  }



  /**

   * After delete actions

   *

   * @return $this

   */

  public function afterDelete()

  {

    // Clear cache when deleting

    self::clearTreeCache();



    return parent::afterDelete();
  }
}
