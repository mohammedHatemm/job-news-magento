<?php

namespace News\Manger\Model;

use News\Manger\Api\NewsRepositoryInterface;
use News\Manger\Api\Data\NewsInterface;
use News\Manger\Api\Data\NewsSearchResultsInterfaceFactory;
use News\Manger\Api\Data\CategorySearchResultsInterfaceFactory;
use News\Manger\Model\ResourceModel\News as NewsResource;
use News\Manger\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class NewsRepository implements NewsRepositoryInterface
{
  /**
   * @var NewsFactory
   */
  protected $newsFactory;

  /**
   * @var NewsResource
   */
  protected $newsResource;

  /**
   * @var NewsCollectionFactory
   */
  protected $newsCollectionFactory;

  /**
   * @var NewsSearchResultsInterfaceFactory
   */
  protected $searchResultsFactory;

  /**
   * @var CategorySearchResultsInterfaceFactory
   */
  protected $categorySearchResultsFactory;

  /**
   * @var CollectionProcessorInterface
   */
  protected $collectionProcessor;

  /**
   * @param NewsFactory $newsFactory
   * @param NewsResource $newsResource
   * @param NewsCollectionFactory $newsCollectionFactory
   * @param NewsSearchResultsInterfaceFactory $searchResultsFactory
   * @param CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
   * @param CollectionProcessorInterface $collectionProcessor
   */
  public function __construct(
    NewsFactory $newsFactory,
    NewsResource $newsResource,
    NewsCollectionFactory $newsCollectionFactory,
    NewsSearchResultsInterfaceFactory $searchResultsFactory,
    CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
    CollectionProcessorInterface $collectionProcessor = null
  ) {
    $this->newsFactory = $newsFactory;
    $this->newsResource = $newsResource;
    $this->newsCollectionFactory = $newsCollectionFactory;
    $this->searchResultsFactory = $searchResultsFactory;
    $this->categorySearchResultsFactory = $categorySearchResultsFactory;
    $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
      \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
    );
  }

  /**
   * @inheritDoc
   */
  public function save(NewsInterface $news)
  {
    try {
      $this->newsResource->save($news);
    } catch (\Exception $exception) {
      throw new CouldNotSaveException(__($exception->getMessage()));
    }
    return $news;
  }

  /**
   * @inheritDoc
   */
  public function getById($newsId)
  {
    $news = $this->newsFactory->create();
    $this->newsResource->load($news, $newsId);
    if (!$news->getId()) {
      throw new NoSuchEntityException(__('The news with the "%1" ID doesn\'t exist.', $newsId));
    }
    return $news;
  }

  /**
   * @inheritDoc
   */
  public function getList(SearchCriteriaInterface $searchCriteria)
  {
    $collection = $this->newsCollectionFactory->create();
    $this->collectionProcessor->process($searchCriteria, $collection);

    $searchResults = $this->searchResultsFactory->create();
    $searchResults->setSearchCriteria($searchCriteria);
    $searchResults->setItems($collection->getItems());
    $searchResults->setTotalCount($collection->getSize());

    return $searchResults;
  }

  /**
   * @inheritDoc
   */
  public function delete(NewsInterface $news)
  {
    try {
      $this->newsResource->delete($news);
    } catch (\Exception $exception) {
      throw new CouldNotDeleteException(__($exception->getMessage()));
    }
    return true;
  }

  /**
   * @inheritDoc
   */
  public function deleteById($newsId)
  {
    return $this->delete($this->getById($newsId));
  }

  /**
   * @inheritDoc
   */
  public function getCategories($newsId)
  {
    $news = $this->getById($newsId);
    $categoryIds = $news->getCategoryIds();

    // This would need to be implemented with actual category collection
    // For now, returning empty result set
    $searchResults = $this->categorySearchResultsFactory->create();
    $searchResults->setItems([]);
    $searchResults->setTotalCount(0);

    return $searchResults;
  }

  /**
   * @inheritDoc
   */
  public function addCategory($newsId, $categoryId)
  {
    $news = $this->getById($newsId);
    $categoryIds = $news->getCategoryIds();

    if (!in_array($categoryId, $categoryIds)) {
      $categoryIds[] = $categoryId;
      $news->setCategoryIds($categoryIds);
      $this->save($news);
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function removeCategory($newsId, $categoryId)
  {
    $news = $this->getById($newsId);
    $categoryIds = $news->getCategoryIds();

    $key = array_search($categoryId, $categoryIds);
    if ($key !== false) {
      unset($categoryIds[$key]);
      $news->setCategoryIds(array_values($categoryIds));
      $this->save($news);
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function setCategories($newsId, array $categoryIds)
  {
    $news = $this->getById($newsId);
    $news->setCategoryIds($categoryIds);
    $this->save($news);

    return true;
  }
}
