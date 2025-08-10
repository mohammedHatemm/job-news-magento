<?php
namespace News\Manger\Block\User\News\Index;

/**
 * Interceptor class for @see \News\Manger\Block\User\News\Index
 */
class Interceptor extends \News\Manger\Block\User\News\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \News\Manger\Model\ResourceModel\News\CollectionFactory $newsCollectionFactory, \News\Manger\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory, \Magento\Framework\App\ResourceConnection $resourceConnection, \Psr\Log\LoggerInterface $logger, \Magento\Framework\App\RequestInterface $request, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $newsCollectionFactory, $categoryCollectionFactory, $resourceConnection, $logger, $request, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getNewsCollection');
        return $pluginInfo ? $this->___callPlugins('getNewsCollection', func_get_args(), $pluginInfo) : parent::getNewsCollection();
    }
}
