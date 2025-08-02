<?php

namespace News\Manger\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\DataObjectHelper;
use News\Manger\Api\NewsRepositoryInterface;
use News\Manger\Api\Data\NewsInterface;
use News\Manger\Api\Data\NewsInterfaceFactory;
use News\Manger\Api\Data\NewsSearchResultsInterfaceFactory;
use News\Manger\Api\Data\CategorySearchResultsInterfaceFactory;
use News\Manger\Model\ResourceModel\News as NewsResource;
use News\Manger\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
use News\Manger\Model\NewsFactory;
use Psr\Log\LoggerInterface;

class NewsRepository implements NewsRepositoryInterface
{
  /**
   * @var NewsFactory
   */
  protected $newsFactory;

  /**
   * @var NewsResource
   */
  protected $resource;

  /**
   * @var NewsCollectionFactory
   */
  protected $collectionFactory;

  /**
   * @var NewsInterfaceFactory
   */
  protected $dataNewsFactory;

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
   * @var SearchCriteriaBuilder
   */
  protected $searchCriteriaBuilder;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var DataObjectHelper
   */
  protected $dataObjectHelper;

  public function __construct(
    NewsFactory $newsFactory,
    NewsResource $resource,
    NewsCollectionFactory $collectionFactory,
    NewsInterfaceFactory $dataNewsFactory,
    NewsSearchResultsInterfaceFactory $searchResultsFactory,
    CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
    CollectionProcessorInterface $collectionProcessor,
    SearchCriteriaBuilder $searchCriteriaBuilder,
    DataObjectHelper $dataObjectHelper,
    LoggerInterface $logger
  ) {
    $this->newsFactory = $newsFactory;
    $this->resource = $resource;
    $this->collectionFactory = $collectionFactory;
    $this->dataNewsFactory = $dataNewsFactory;
    $this->searchResultsFactory = $searchResultsFactory;
    $this->categorySearchResultsFactory = $categorySearchResultsFactory;
    $this->collectionProcessor = $collectionProcessor;
    $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    $this->dataObjectHelper = $dataObjectHelper;
    $this->logger = $logger;
  }

  /**
   * @inheritDoc
   */
  public function save(NewsInterface $news)
  {
    try {
      // Validate data
      $this->validate($news);

      $model = null;

      // If there's an ID, load existing news
      if ($news->getNewsId() && is_numeric($news->getNewsId())) {
        try {
          $model = $this->newsFactory->create();
          $this->resource->load($model, $news->getNewsId());
          if (!$model->getId()) {
            throw new NoSuchEntityException(__('News with id "%1" does not exist.', $news->getNewsId()));
          }
        } catch (\Exception $e) {
          $this->logger->error('Error loading news: ' . $e->getMessage());
          throw new NoSuchEntityException(__('News with id "%1" does not exist.', $news->getNewsId()));
        }
      } else {
        // Create new model
        $model = $this->newsFactory->create();
      }

      // Set data
      $model->setData([
        'news_title' => $news->getNewsTitle(),
        'news_content' => $news->getNewsContent(),
        'news_status' => $news->getNewsStatus() ?? 1,
        'category_ids' => $news->getCategoryIds() ?: []
      ]);

      // Set ID if updating
      if ($news->getNewsId()) {
        $model->setId($news->getNewsId());
      }

      $this->resource->save($model);

      // Update the data object with saved values
      $news->setNewsId($model->getId());
      $news->setCreatedAt($model->getCreatedAt());
      $news->setUpdatedAt($model->getUpdatedAt());
      $news->setCategoryIds($model->getCategoryIds());

      return $news;
    } catch (\Exception $e) {
      $this->logger->error('Error saving news: ' . $e->getMessage());
      throw new CouldNotSaveException(__('Could not save news: %1', $e->getMessage()));
    }
  }

  /**
   * @inheritDoc
   */
  public function getById($newsId)
  {
    $newsModel = $this->newsFactory->create();
    $this->resource->load($newsModel, $newsId);

    if (!$newsModel->getId()) {
      throw new NoSuchEntityException(__('News with id "%1" does not exist.', $newsId));
    }

    // Convert model to data object
    $newsData = $this->dataNewsFactory->create();
    $newsData->setNewsId($newsModel->getId());
    $newsData->setNewsTitle($newsModel->getNewsTitle());
    $newsData->setNewsContent($newsModel->getNewsContent());
    $newsData->setNewsStatus($newsModel->getNewsStatus());
    $newsData->setCreatedAt($newsModel->getCreatedAt());
    $newsData->setUpdatedAt($newsModel->getUpdatedAt());
    $newsData->setCategoryIds($newsModel->getCategoryIds());

    return $newsData;
  }

  /**
   * @inheritDoc
   */
  public function getList(SearchCriteriaInterface $searchCriteria = null)
  {
    $collection = $this->collectionFactory->create();

    if ($searchCriteria === null) {
      $searchCriteria = $this->searchCriteriaBuilder->create();
    }

    $this->collectionProcessor->process($searchCriteria, $collection);

    $searchResults = $this->searchResultsFactory->create();
    $searchResults->setSearchCriteria($searchCriteria);

    // Convert models to data objects
    $items = [];
    foreach ($collection->getItems() as $model) {
      $newsData = $this->dataNewsFactory->create();
      $newsData->setNewsId($model->getId());
      $newsData->setNewsTitle($model->getNewsTitle());
      $newsData->setNewsContent($model->getNewsContent());
      $newsData->setNewsStatus($model->getNewsStatus());
      $newsData->setCreatedAt($model->getCreatedAt());
      $newsData->setUpdatedAt($model->getUpdatedAt());
      $newsData->setCategoryIds($model->getCategoryIds());

      $items[] = $newsData;
    }

    $searchResults->setItems($items);
    $searchResults->setTotalCount($collection->getSize());

    return $searchResults;
  }

  /**
   * @inheritDoc
   */
  public function delete(NewsInterface $news)
  {
    try {
      $newsModel = $this->newsFactory->create();
      $this->resource->load($newsModel, $news->getNewsId());

      if (!$newsModel->getId()) {
        throw new NoSuchEntityException(__('News with id "%1" does not exist.', $news->getNewsId()));
      }

      $this->resource->delete($newsModel);
      return true;
    } catch (\Exception $e) {
      throw new CouldNotDeleteException(__('Could not delete news: %1', $e->getMessage()));
    }
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
    // Implementation for getting categories for a news item
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
    try {
      // Get current news
      $news = $this->getById($newsId);
      $currentCategories = $news->getCategoryIds();

      // Add category if not already present
      if (!in_array($categoryId, $currentCategories)) {
        $currentCategories[] = $categoryId;
        $news->setCategoryIds($currentCategories);
        $this->save($news);
      }

      return true;
    } catch (\Exception $e) {
      $this->logger->error('Error adding category to news: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * @inheritDoc
   */
  public function removeCategory($newsId, $categoryId)
  {
    try {
      // Get current news
      $news = $this->getById($newsId);
      $currentCategories = $news->getCategoryIds();

      // Remove category
      $currentCategories = array_filter($currentCategories, function ($id) use ($categoryId) {
        return $id != $categoryId;
      });

      $news->setCategoryIds(array_values($currentCategories));
      $this->save($news);

      return true;
    } catch (\Exception $e) {
      $this->logger->error('Error removing category from news: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * @inheritDoc
   */
  public function setCategories($newsId, array $categoryIds)
  {
    try {
      $news = $this->getById($newsId);
      $news->setCategoryIds($categoryIds);
      $this->save($news);
      return true;
    } catch (\Exception $e) {
      $this->logger->error('Error setting categories for news: ' . $e->getMessage());
      return false;
    }
  }



  /**
   * @inheritDoc
   */
  public function validate(NewsInterface $news)
  {
    if (empty($news->getNewsTitle())) {
      throw new LocalizedException(__('News title is required.'));
    }

    if (empty($news->getNewsContent())) {
      throw new LocalizedException(__('News content is required.'));
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function exists($newsId): bool
  {
    try {
      $this->getById($newsId);
      return true;
    } catch (NoSuchEntityException $e) {
      return false;
    }
  }
}
