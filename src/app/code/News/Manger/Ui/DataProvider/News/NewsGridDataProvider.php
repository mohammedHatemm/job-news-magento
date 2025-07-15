<?php

namespace News\Manger\Ui\DataProvider\News;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\News\CollectionFactory; // ✅ استخدم الـ Factory
use Psr\Log\LoggerInterface;

class NewsGridDataProvider extends AbstractDataProvider
{
  protected $collection;
  private $logger;

  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    CollectionFactory $collectionFactory, // ✅ Factory
    LoggerInterface $logger,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collectionFactory->create(); // ✅ استخدم factory لإنشاء الكولكشن
    $this->logger = $logger;
    parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
  }

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
      'items' => array_values($this->loadedData),
    ];
  }
}
