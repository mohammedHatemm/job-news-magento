<?php

namespace News\Manger\Model\ResourceModel\Category;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
  protected $_idFieldName = 'category_id';
  protected $_eventPrefix = 'news_manger_category_collection';
  protected $_eventObject = 'category_collection';

  /**
   * Define the resource model & the model.
   *
   * @return void
   */
  protected function _construct()
  {
    $this->_init('News\Manger\Model\Category', 'News\Manger\Model\ResourceModel\Category');
  }
}
