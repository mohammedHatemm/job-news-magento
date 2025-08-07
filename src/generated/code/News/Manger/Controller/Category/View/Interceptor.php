<?php
namespace News\Manger\Controller\Category\View;

/**
 * Interceptor class for @see \News\Manger\Controller\Category\View
 */
class Interceptor extends \News\Manger\Controller\Category\View implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \News\Manger\Model\CategoryRepository $categoryRepository, \News\Manger\Model\ResourceModel\News\CollectionFactory $newsCollection)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $categoryRepository, $newsCollection);
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
