<?php

namespace News\Manger\Model\ResourceModel\News\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use News\Manger\Model\ResourceModel\News\Collection as NewsCollection;

class Collection extends NewsCollection implements SearchResultInterface
{
  protected $aggregations;

  public function __construct(
    \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
    \Magento\Framework\Event\ManagerInterface $eventManager,
    $mainTable,
    $eventPrefix,
    $eventObject,
    $resourceModel,
    $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
    $connection = null,
    \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
  ) {
    parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    $this->_eventPrefix = $eventPrefix;
    $this->_eventObject = $eventObject;
    $this->_init($model, $resourceModel);
    $this->setMainTable($mainTable);
  }

  protected function _initSelect()
  {
    parent::_initSelect();

    // Join with parent category to get parent name
    $this->getSelect()->joinLeft(
      ['parent' => $this->getTable('news_category')],
      'main_table.parent_id = parent.category_id',
      ['parent_name' => 'IFNULL(parent.category_name, "No Parent")']
    );

    // Log the SQL query for debugging
    $this->_logger->debug('Grid Collection SQL: ' . $this->getSelect()->__toString());

    return $this;
  }
  public function getAggregations()
  {
    return $this->aggregations;
  }

  public function setAggregations($aggregations)
  {
    $this->aggregations = $aggregations;
  }

  public function getAllIds($limit = null, $offset = null)
  {
    return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
  }

  public function getSearchCriteria()
  {
    return null;
  }

  public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
  {
    return $this;
  }

  public function getTotalCount()
  {
    return $this->getSize();
  }

  public function setTotalCount($totalCount)
  {
    return $this;
  }

  public function setItems(array $items = null)
  {
    return $this;
  }
}
