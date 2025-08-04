<?php
namespace News\Manger\Model\Resolver\CreateNews;

/**
 * Interceptor class for @see \News\Manger\Model\Resolver\CreateNews
 */
class Interceptor extends \News\Manger\Model\Resolver\CreateNews implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\News\Manger\Api\NewsRepositoryInterface $newsRepository, \News\Manger\Api\Data\NewsInterfaceFactory $newsFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($newsRepository, $newsFactory, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(\Magento\Framework\GraphQl\Config\Element\Field $field, $context, \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'resolve');
        return $pluginInfo ? $this->___callPlugins('resolve', func_get_args(), $pluginInfo) : parent::resolve($field, $context, $info, $value, $args);
    }
}
