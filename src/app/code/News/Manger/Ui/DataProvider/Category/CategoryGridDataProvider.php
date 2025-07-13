<?php

namespace News\Manger\Ui\DataProvider\Category;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\Category\Collection;
use Psr\Log\LoggerInterface;

class CategoryGridDataProvider extends AbstractDataProvider
{
  protected $loadedData;
  private $logger;

  /**
   * @param string $name
   * @param string $primaryFieldName
   * @param string $requestFieldName
   * @param Collection $collection
   * @param LoggerInterface $logger
   * @param array $meta
   * @param array $data
   */
  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    Collection $collection,
    LoggerInterface $logger,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collection;
    $this->collection->joinParentCategory();
    $this->logger = $logger;
    parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
  }

  /**
   * Get data
   *
   * @return array
   */
  public function getData()
  {
    if (isset($this->loadedData)) {
      return $this->loadedData;
    }

    $items = $this->collection->getItems();
    $this->loadedData = [];

    foreach ($items as $item) {
      $this->loadedData[$item->getId()] = $item->getData();
    }

    return [
      'totalRecords' => $this->collection->getSize(),
      'items' => array_values($this->loadedData)
    ];
  }
}
