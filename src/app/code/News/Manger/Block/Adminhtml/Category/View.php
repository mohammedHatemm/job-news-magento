<?php

namespace News\Manger\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Container;
use Magento\Framework\Registry;

class View extends Container
{
  protected $registry;

  public function __construct(
    \Magento\Backend\Block\Widget\Context $context,
    Registry $registry,
    array $data = []
  ) {
    $this->registry = $registry;
    parent::__construct($context, $data);
  }

  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('News_Manger::category/view/view.phtml');

    // Add back button
    $this->buttonList->add(
      'back',
      [
        'label' => __('Back'),
        'onclick' => "setLocation('" . $this->getBackUrl() . "')",
        'class' => 'back'
      ]
    );
  }

  public function getBackUrl()
  {
    return $this->getUrl('*/*/');
  }

  public function getCategory()
  {
    return $this->registry->registry('current_category');
  }
}
