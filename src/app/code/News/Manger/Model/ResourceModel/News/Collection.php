<?php

namespace News\Manger\Model\ResourceModel\News;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
  protected $_idFieldName = 'news_id';
  protected $_eventPrefix = 'news_manger_news_collection';
  protected $_eventObject = 'news_collection';

  /**
   * Define the resource model & the model.
   *
   * @return void
   */
  protected function _construct()
  {
    $this->_init('News\Manger\Model\News', 'News\Manger\Model\ResourceModel\News');
  }
}
