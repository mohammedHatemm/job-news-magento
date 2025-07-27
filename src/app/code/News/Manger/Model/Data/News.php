<?php

namespace News\Manger\Model\Data;

use News\Manger\Api\Data\NewsInterface;
use Magento\Framework\DataObject;

class News extends DataObject implements NewsInterface
{
  /**
   * @inheritDoc
   */
  public function getId()
  {
    return $this->getData(self::NEWS_ID);
  }

  /**
   * @inheritDoc
   */
  public function setId($id)
  {
    return $this->setData(self::NEWS_ID, $id);
  }

  /**
   * @inheritDoc
   */
  public function getTitle()
  {
    return $this->getData(self::TITLE);
  }

  /**
   * @inheritDoc
   */
  public function setTitle($title)
  {
    return $this->setData(self::TITLE, $title);
  }

  /**
   * @inheritDoc
   */
  public function getContent()
  {
    return $this->getData(self::CONTENT);
  }

  /**
   * @inheritDoc
   */
  public function setContent($content)
  {
    return $this->setData(self::CONTENT, $content);
  }

  /**
   * @inheritDoc
   */
  public function getStatus()
  {
    return $this->getData(self::STATUS);
  }

  /**
   * @inheritDoc
   */
  public function setStatus($status)
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
  public function getUpdatedAt()
  {
    return $this->getData(self::UPDATED_AT);
  }

  /**
   * @inheritDoc
   */
  public function getCategoryIds()
  {
    $ids = $this->getData(self::CATEGORY_IDS);
    return is_array($ids) ? $ids : [];
  }

  /**
   * @inheritDoc
   */
  public function setCategoryIds(array $categoryIds)
  {
    return $this->setData(self::CATEGORY_IDS, $categoryIds);
  }
}
