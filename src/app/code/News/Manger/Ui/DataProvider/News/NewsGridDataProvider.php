<?php

namespace News\Manger\Ui\DataProvider\News;

use Magento\Ui\DataProvider\AbstractDataProvider;
// هذا هو المسار الصحيح لكلاس الكولكشن الذي يجب استدعائه
use News\Manger\Model\ResourceModel\News\Collection;
use Psr\Log\LoggerInterface;

class NewsGridDataProvider extends AbstractDataProvider
{
  protected $loadedData;
  private $logger;

  /**
   * @param string $name
   * @param string $primaryFieldName
   * @param string $requestFieldName
   * @param Collection $collection The Grid Collection
   * @param LoggerInterface $logger
   * @param array $meta
   * @param array $data
   */
  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    Collection $collection, // تأكد من أن الـ Type Hint هنا صحيح
    LoggerInterface $logger,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collection;
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
