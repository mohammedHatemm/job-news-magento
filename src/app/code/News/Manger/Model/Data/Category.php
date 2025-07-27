<?php

namespace News\Manger\Model\Data;

use News\Manger\Api\Data\CategoryInterface;
use Magento\Framework\DataObject;

class Category extends DataObject implements CategoryInterface
{
  /**
   * @inheritDoc
   */
  public function getCategoryId()
  {
    return $this->getData(self::CATEGORY_ID);
  }

  /**
   * @inheritDoc
   */
  public function setCategoryId($id)
  {
    return $this->setData(self::CATEGORY_ID, $id);
  }

  /**
   * @inheritDoc
   */
  public function getCategoryName()
  {
    return $this->getData(self::NAME);
  }

  /**
   * @inheritDoc
   */
  public function setCategoryName($name)
  {
    return $this->setData(self::NAME, $name);
  }

  /**
   * @inheritDoc
   */
  public function getCategoryDescription()
  {
    return $this->getData(self::DESCRIPTION);
  }

  /**
   * @inheritDoc
   */
  public function setCategoryDescription($description)
  {
    return $this->setData(self::DESCRIPTION, $description);
  }

  /**
   * @inheritDoc
   */
  public function getCategoryStatus()
  {
    return $this->getData(self::STATUS);
  }

  /**
   * @inheritDoc
   */
  public function setCategoryStatus($status)
  {
    return $this->setData(self::STATUS, $status);
  }

  /**
   * @inheritDoc
   */
  public function getCreatedAt()
  {
    return $this->getData(self::CREATED_AT);
  }

  /**
   * @inheritDoc
   */
  public function setCreatedAt($createdAt)
  {
    return $this->setData(self::CREATED_AT, $createdAt);
  }

  /**
   * @inheritDoc
   */
  public function getUpdatedAt()
  {
    return $this->getData(self::UPDATED_AT);
  }

  /**
   * @inheritDoc
   */
  public function setUpdatedAt($updatedAt)
  {
    return $this->setData(self::UPDATED_AT, $updatedAt);
  }

  /**
   * @inheritDoc
   */
  public function getParentIds()
  {
    $ids = $this->getData(self::PARENT_IDS);
    return is_array($ids) ? $ids : [];
  }

  /**
   * @inheritDoc
   */
  public function setParentIds($parentIds)
  {
    if (!is_array($parentIds)) {
      $parentIds = json_decode($parentIds, true) ?: [];
    }
    return $this->setData(self::PARENT_IDS, $parentIds);
  }

  /**
   * @inheritDoc
   */
  public function getChildIds()
  {
    $ids = $this->getData(self::CHILD_IDS);
    return is_array($ids) ? $ids : [];
  }

  /**
   * @inheritDoc
   */
  public function setChildIds($childIds)
  {
    if (!is_array($childIds)) {
      $childIds = json_decode($childIds, true) ?: [];
    }
    return $this->setData(self::CHILD_IDS, $childIds);
  }

  /**
   * @inheritDoc
   */
  public function getNewsIds()
  {
    $ids = $this->getData(self::NEWS_IDS);
    return is_array($ids) ? $ids : [];
  }

  /**
   * @inheritDoc
   */
  public function setNewsIds($newsIds)
  {
    if (!is_array($newsIds)) {
      $newsIds = json_decode($newsIds, true) ?: [];
    }
    return $this->setData(self::NEWS_IDS, $newsIds);
  }

  /**
   * Retrieve existing extension attributes object or create a new one.
   *
   * @return \News\Manger\Api\Data\CategoryExtensionInterface|null
   */
  public function getExtensionAttributes()
  {
    return $this->_getExtensionAttributes();
  }

  /**
   * Set an extension attributes object.
   *
   * @param \News\Manger\Api\Data\CategoryExtensionInterface $extensionAttributes
   * @return $this
   */
  public function setExtensionAttributes(\News\Manger\Api\Data\CategoryExtensionInterface $extensionAttributes)
  {
    return $this->_setExtensionAttributes($extensionAttributes);
  }
}
