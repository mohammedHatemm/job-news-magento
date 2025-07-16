<?php

namespace News\Manger\Block\Adminhtml\Category\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Hierarchy extends Template
{
  protected $registry;

  public function __construct(
    Context $context,
    Registry $registry,
    array $data = []
  ) {
    $this->registry = $registry;
    parent::__construct($context, $data);
  }

  public function getBreadcrumbPath()
  {
    $category = $this->registry->registry('current_category');
    return $category ? $category->getBreadcrumbPath() : [];
  }
}
