<?php

namespace News\Manger\Ui\DataProvider\News;

use Magento\Ui\DataProvider\AbstractDataProvider;
use News\Manger\Model\ResourceModel\News\CollectionFactory;
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
    CollectionFactory $collectionFactory,
    LoggerInterface $logger,
    array $meta = [],
    array $data = []
  ) {
    $this->collection = $collectionFactory->create();
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

      // ✅ Add parent_name logic
      // if (isset($data['parent_id']) && $data['parent_id']) {
      //   try {
      //     $parent = $this->collection->getItemById($data['parent_id']);
      //     $data['parent_name'] = $parent ? $parent->getData('news_title') : __('Unknown Parent');
      //   } catch (\Exception $e) {
      //     $data['parent_name'] = __('Error');
      //     $this->logger->error('Error getting parent: ' . $e->getMessage());
      //   }
      // } else {
      //   $data['parent_name'] = __('No Parent');
      // }

      $this->loadedData[$item->getId()] = $data;
    }

    return [
      'totalRecords' => $this->collection->getSize(),
      'items' => array_values($this->loadedData),
    ];
  }
}
