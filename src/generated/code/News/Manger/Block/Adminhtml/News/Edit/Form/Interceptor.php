<?php
namespace News\Manger\Block\Adminhtml\News\Edit\Form;

/**
 * Interceptor class for @see \News\Manger\Block\Adminhtml\News\Edit\Form
 */
class Interceptor extends \News\Manger\Block\Adminhtml\News\Edit\Form implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \News\Manger\Model\CategoryFactory $categoryFactory, \Psr\Log\LoggerInterface $logger, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $categoryFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getForm');
        return $pluginInfo ? $this->___callPlugins('getForm', func_get_args(), $pluginInfo) : parent::getForm();
    }
}
