<?php
/*
File: app/code/News/Manger/Block/User/News/Index.php
*/

namespace News\Manger\Block\User\News;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use News\Manger\Model\ResourceModel\News\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Index extends Template
{
  protected $newsCollectionFactory;
  protected $resourceConnection;
  protected $logger;

  public function __construct(
    Context $context,
    CollectionFactory $newsCollectionFactory,
    ResourceConnection $resourceConnection,
    LoggerInterface $logger,
    array $data = []
  ) {
    parent::__construct($context, $data);
    $this->newsCollectionFactory = $newsCollectionFactory;
    $this->resourceConnection = $resourceConnection;
    $this->logger = $logger;
  }

  public function getNewsCollection()
  {
    $collection = $this->newsCollectionFactory->create();
    $collection->addFieldToFilter('news_status', 1);
    $collection->setOrder('created_at', 'DESC');
    return $collection;
  }

  /**
   * جلب أسماء التصنيفات المباشرة للخبر
   * @param int $newsId
   * @return array
   */
  public function getCategoriesForNews($newsId)
  {
    try {
      $connection = $this->resourceConnection->getConnection();
      $select = $connection->select()
        ->from(['nac' => $this->resourceConnection->getTableName('news_news')], [])
        ->joinLeft(
          ['nc' => $this->resourceConnection->getTableName('news_category')],
          'nac.category_id = nc.category_id',
          ['name' => 'category_name']
        )
        ->where('nac.news_id = ?', $newsId);

      return $connection->fetchCol($select);
    } catch (\Exception $e) {
      $this->logger->error('Error in getCategoriesForNews: ' . $e->getMessage());
      return [];
    }
  }

  public function getExcerpt(string $content, int $length = 150): string
  {
    return mb_substr(strip_tags($content), 0, $length) . '...';
  }
}
