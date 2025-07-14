<?php

namespace News\Manger\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Category extends AbstractModel implements IdentityInterface
{
  const CACHE_TAG = 'news_manger_category';
  const CATEGORY_ID = 'category_id';

  protected $_cacheTag = self::CACHE_TAG;
  protected $_eventPrefix = 'news_manger_category';
  protected $_eventObject = 'category';
  protected $_idFieldName = self::CATEGORY_ID;

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
   * Get category ID
   *
   * @return int|null
   */
  public function getCategoryId()
  {
    return $this->getData(self::CATEGORY_ID);
  }

  /**
   * Set category ID
   *
   * @param int $categoryId
   * @return $this
   */
  public function setCategoryId($categoryId)
  {
    return $this->setData(self::CATEGORY_ID, $categoryId);
  }

  /**
   * Get category name
   *
   * @return string|null
   */
  public function getCategoryName()
  {
    return $this->getData('category_name');
  }

  /**
   * Set category name
   *
   * @param string $categoryName
   * @return $this
   */
  public function setCategoryName($categoryName)
  {
    return $this->setData('category_name', $categoryName);
  }

  /**
   * Get category description
   *
   * @return string|null
   */
  public function getCategoryDescription()
  {
    return $this->getData('category_description');
  }

  /**
   * Set category description
   *
   * @param string $categoryDescription
   * @return $this
   */
  public function setCategoryDescription($categoryDescription)
  {
    return $this->setData('category_description', $categoryDescription);
  }

  /**
   * Get category status
   *
   * @return int|null
   */
  public function getCategoryStatus()
  {
    return $this->getData('category_status');
  }

  /**
   * Set category status
   *
   * @param int $categoryStatus
   * @return $this
   */
  public function setCategoryStatus($categoryStatus)
  {
    return $this->setData('category_status', $categoryStatus);
  }

  /**
   * Get parent ID
   *
   * @return int|null
   */
  public function getParentId()
  {
    return $this->getData('parent_id');
  }

  /**
   * Set parent ID
   *
   * @param int $parentId
   * @return $this
   */
  public function setParentId($parentId)
  {
    return $this->setData('parent_id', $parentId);
  }

  /**
   * Get parent name (if loaded via join)
   *
   * @return string|null
   */
  public function getParentName()
  {
    return $this->getData('parent_name');
  }

  /**
   * Set parent name
   *
   * @param string $parentName
   * @return $this
   */
  public function setParentName($parentName)
  {
    return $this->setData('parent_name', $parentName);
  }

  /**
   * Get created at
   *
   * @return string|null
   */
  public function getCreatedAt()
  {
    return $this->getData('created_at');
  }

  /**
   * Set created at
   *
   * @param string $createdAt
   * @return $this
   */
  public function setCreatedAt($createdAt)
  {
    return $this->setData('created_at', $createdAt);
  }

  /**
   * Get updated at
   *
   * @return string|null
   */
  public function getUpdatedAt()
  {
    return $this->getData('updated_at');
  }

  /**
   * Set updated at
   *
   * @param string $updatedAt
   * @return $this
   */
  public function setUpdatedAt($updatedAt)
  {
    return $this->setData('updated_at', $updatedAt);
  }

  /**
   * Check if category is active
   *
   * @return bool
   */
  public function isActive()
  {
    return (bool)$this->getCategoryStatus();
  }

  /**
   * Before save actions
   *
   * @return $this
   */
  public function beforeSave()
  {
    if (!$this->getId()) {
      $this->setCreatedAt(date('Y-m-d H:i:s'));
    }
    $this->setUpdatedAt(date('Y-m-d H:i:s'));

    return parent::beforeSave();
  }

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

    return $this->_getResource()->load($this->getParentId());
  }

  /**
   * Get children categories
   *
   * @return \News\Manger\Model\ResourceModel\Category\Collection
   */
  public function getChildrenCategories()
  {
    $collection = $this->getCollection();
    $collection->addFieldToFilter('parent_id', $this->getId());
    return $collection;
  }
}
