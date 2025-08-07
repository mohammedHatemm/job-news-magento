<?php

namespace News\Manger\Block\Category;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class View extends Template
{
  protected $categoryRepository;
  protected $newsCollection;

  public function __construct(
    Context $context,
    \News\Manger\Model\CategoryRepository $categoryRepository,
    \News\Manger\Model\ResourceModel\News\CollectionFactory $newsCollection,
    array $data = []
  ) {
    parent::__construct($context, $data);
    $this->categoryRepository = $categoryRepository;
    $this->newsCollection = $newsCollection;
  }

  /**
   * Get category by ID
   *
   * @return \News\Manger\Model\Category|null
   */
  public function getCategory()
  {
    $categoryId = $this->getRequest()->getParam('id');
    try {
      return $this->categoryRepository->getById($categoryId);
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Get news collection for current category
   *
   * @return \News\Manger\Model\ResourceModel\News\Collection
   */
  public function getCategoryNews()
  {
    $categoryId = $this->getRequest()->getParam('id');
    $collection = $this->newsCollection->create();
    $collection->addCategoryFilter($categoryId)
      ->addFieldToFilter('news_status', 1)
      ->setOrder('created_at', 'DESC');

    return $collection;
  }
}
