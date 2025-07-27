<?php

namespace News\Manger\Model\Data;

use Magento\Framework\ObjectManagerInterface;
use News\Manger\Api\Data\CategoryInterface;

class CategoryFactory
{
  /**
   * @var ObjectManagerInterface
   */
  protected $objectManager;

  /**
   * @param ObjectManagerInterface $objectManager
   */
  public function __construct(ObjectManagerInterface $objectManager)
  {
    $this->objectManager = $objectManager;
  }

  /**
   * Create a new Category data object
   *
   * @param array $data
   * @return CategoryInterface
   */
  public function create(array $data = [])
  {
    return $this->objectManager->create(\News\Manger\Model\Data\Category::class, ['data' => $data]);
  }
}
