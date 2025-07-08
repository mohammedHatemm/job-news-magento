<?php

namespace News\Manger\Model;

class News extends \Magento\Framework\Model\AbstractModel
{
  const NEWS_ID = "news_id";
  protected $_eventPrefix = "news";
  protected $eventObject = "news";
  protected $_idFieldName = self::NEWS_ID;

  protected function _construct()
  {
    $this->_init("News\Manger\Model\ResourceModel\News");
  }
}
