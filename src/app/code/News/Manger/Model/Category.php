<?php

namespace News\Manger\Model;

class Category extends \Magento\Framework\Model\AbstractModel

{

  const CATEGORY_ID = "category_id";
  protected $_eventPrefix = "category";
  protected $_eventObject = "category";
  protected $_idFieldName = self::CATEGORY_ID;

  protected function _construct()
  {
    $this->_init("News\Manger\Model\ResourceModel\Category");
  }
}
