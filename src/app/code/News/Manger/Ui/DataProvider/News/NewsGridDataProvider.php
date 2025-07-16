<?php

namespace News\Manger\Ui\DataProvider\News;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\News\Grid\Collection;
use Psr\Log\LoggerInterface;

class NewsGridDataProvider extends AbstractDataProvider
{
  protected $collection;

  /** @var LoggerInterface */
  private $logger;

  /** @var array */
  protected array $loadedData = [];

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
    $this->logger = $logger;
    parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
  }

  public function getData()
  {
    if (!empty($this->loadedData)) {
      return [
        'totalRecords' => $this->collection->getSize(),
        'items' => array_values($this->loadedData),
      ];
    }

    foreach ($this->collection->getItems() as $item) {
      $data = $item->getData();

      // Ensure parent_name fallback
      if (!isset($data['parent_name']) || $data['parent_name'] === null) {
        $data['parent_name'] = __('No Parent');
      }

      $this->loadedData[$item->getId()] = $data;
    }

    return [
      'totalRecords' => $this->collection->getSize(),
      'items' => array_values($this->loadedData),
    ];
  }
}
