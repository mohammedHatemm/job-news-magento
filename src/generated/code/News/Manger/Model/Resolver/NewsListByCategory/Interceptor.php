<?php
namespace News\Manger\Model\Resolver\NewsListByCategory;

/**
 * Interceptor class for @see \News\Manger\Model\Resolver\NewsListByCategory
 */
class Interceptor extends \News\Manger\Model\Resolver\NewsListByCategory implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\News\Manger\Api\CategoryRepositoryInterface $categoryRepository)
    {
        $this->___init();
        parent::__construct($categoryRepository);
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
