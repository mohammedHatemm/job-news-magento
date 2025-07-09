<?php

namespace   News\Manger\Ui\DataProvider\News;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\News\CollectionFactory;

class NewsGridDataProvider extends AbstractDataProvider
{
  protected $loadedData;

  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    CollectionFactory $collectionFactory,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collectionFactory->create();
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

      $itemData = [
        'news_id' => $item->getNewsId(),
        'news_title' => $item->getNewsTitle(),
        'news_content' => $item->getNewsContent(),
        'news_created_at' => $item->getNewsCreatedAt(),
        'news_status' => $item->getNewsStatus(),
      ];
      $this->loadedData[] = $itemData;
    }

    return [
      'totalRecords' => $this->collection->getSize(),
      'items' => $this->loadedData
    ];
  }
}
