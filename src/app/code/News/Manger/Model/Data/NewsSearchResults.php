<?php

namespace News\Manger\Model\Data;

use News\Manger\Api\Data\NewsSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with News search results.
 */
class NewsSearchResults extends SearchResults implements NewsSearchResultsInterface
{
  /**
   * @inheritDoc
   */
  public function getItems()
  {
    return $this->_get('items') === null ? [] : $this->_get('items');
  }

  /**
   * @inheritDoc
   */
  public function setItems(array $items)
  {
    return $this->setData('items', $items);
  }
}
