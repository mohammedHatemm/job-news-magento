<?php
namespace News\Manger\Model\Resolver\CategoryList;

/**
 * Interceptor class for @see \News\Manger\Model\Resolver\CategoryList
 */
class Interceptor extends \News\Manger\Model\Resolver\CategoryList implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\News\Manger\Api\CategoryRepositoryInterface $categoryRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Framework\Api\FilterBuilder $filterBuilder, \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder)
    {
        $this->___init();
        parent::__construct($categoryRepository, $searchCriteriaBuilder, $filterBuilder, $filterGroupBuilder);
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
