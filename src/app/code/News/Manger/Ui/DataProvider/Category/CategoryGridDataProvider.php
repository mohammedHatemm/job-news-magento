<?php

namespace News\Manger\Ui\DataProvider\Category;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Api\Search\SearchResultInterface;
use Psr\Log\LoggerInterface;

class CategoryGridDataProvider extends AbstractDataProvider
{
  protected $loadedData;
  private $logger;

  /**
   * @param string $name
   * @param string $primaryFieldName
   * @param string $requestFieldName
   * @param SearchResultInterface $collection
   * @param LoggerInterface $logger
   * @param array $meta
   * @param array $data
   */
  public function __construct(
    $name,
    $primaryFieldName,
    $requestFieldName,
    SearchResultInterface $collection,
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
    if (isset($this->loadedData)) {
      return $this->loadedData;
    }

    try {
      // Log the collection class to make sure we're using the right one
      $this->logger->debug('Collection Class: ' . get_class($this->collection));

      // Log the SQL query
      $this->logger->debug('Collection SQL: ' . $this->collection->getSelect()->__toString());

      $items = $this->collection->getItems();
      $this->logger->debug('Collection Size: ' . $this->collection->getSize());

      $formattedItems = [];
      foreach ($items as $item) {
        $data = $item->getData();

        // Transform category_status
        $data['category_status'] = $data['category_status'] == 1 ? __('Enabled') : __('Disabled');

        // Ensure parent_name exists
        if (!isset($data['parent_name']) || $data['parent_name'] === null) {
          $data['parent_name'] = 'No Parent';
        }

        $formattedItems[] = $data;
      }

      $this->loadedData = [
        'totalRecords' => $this->collection->getSize(),
        'items' => $formattedItems
      ];

      $this->logger->debug('Final LoadedData: ' . json_encode($this->loadedData));
    } catch (\Exception $e) {
      $this->logger->error('CategoryGridDataProvider Error: ' . $e->getMessage());
      $this->logger->error('Stack Trace: ' . $e->getTraceAsString());
      $this->loadedData = [
        'totalRecords' => 0,
        'items' => []
      ];
    }

    return $this->loadedData;
  }

  /**
   * Get data for specific fields
   *
   * @return array
   */
  public function getMeta()
  {
    $meta = parent::getMeta();

    // Add custom meta if needed
    $meta['news_category_columns']['children']['parent_name']['arguments']['data']['config']['visible'] = true;

    return $meta;
  }
}
