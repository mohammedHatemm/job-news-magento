<?php

namespace News\Manger\Model;

class News extends \Magento\Framework\Model\AbstractModel
{
  const NEWS_ID = 'news_id';
  const TITLE = 'news_title';
  const CONTENT = 'news_content';
  const CREATED_AT = 'created_at';
  const STATUS = 'news_status';

  protected $_eventPrefix = 'news';
  protected $_eventObject = 'news';
  protected $_idFieldName = self::NEWS_ID;

  protected function _construct()
  {
    $this->_init(\News\Manger\Model\ResourceModel\News::class);
  }

  // Getters
  public function getId()
  {
    return $this->getData(self::NEWS_ID);
  }

  public function getTitle()
  {
    return $this->getData(self::TITLE);
  }

  public function getContent()
  {
    return $this->getData(self::CONTENT);
  }

  public function getCreatedAt()
  {
    return $this->getData(self::CREATED_AT);
  }

  public function getStatus()
  {
    return $this->getData(self::STATUS);
  }

  // Setters
  public function setId($id)
  {
    return $this->setData(self::NEWS_ID, $id);
  }

  public function setTitle($title)
  {
    return $this->setData(self::TITLE, $title);
  }

  public function setContent($content)
  {
    return $this->setData(self::CONTENT, $content);
  }

  public function setCreatedAt($createdAt)
  {
    return $this->setData(self::CREATED_AT, $createdAt);
  }

  public function setStatus($status)
  {
    return $this->setData(self::STATUS, $status);
  }
}
