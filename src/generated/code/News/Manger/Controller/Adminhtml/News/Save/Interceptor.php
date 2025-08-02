<?php
namespace News\Manger\Controller\Adminhtml\News\Save;

/**
 * Interceptor class for @see \News\Manger\Controller\Adminhtml\News\Save
 */
class Interceptor extends \News\Manger\Controller\Adminhtml\News\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor, \News\Manger\Model\NewsFactory $newsFactory, \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter, \Magento\Framework\App\ResourceConnection $resourceConnection, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($context, $dataPersistor, $newsFactory, $dateFilter, $resourceConnection, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
