<?php

namespace News\Manger\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

use News\Manger\Api\CategoryRepositoryInterface;
use News\Manger\Api\Data\CategoryInterface;
use News\Manger\Api\Data\CategorySearchResultsInterfaceFactory;
use News\Manger\Model\ResourceModel\Category as CategoryResource;
use News\Manger\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

use News\Manger\Model\Data\CategoryFactory as DataCategoryFactory;

/**
 * Category repository class
 */
class CategoryRepository implements CategoryRepositoryInterface
{
  /**
   * @var CategoryResource
   */
  protected $resource;

  /**
   * @var \News\Manger\Model\CategoryFactory
   */
  protected $categoryFactory;

  /**
   * @var DataCategoryFactory
   */
  protected $dataCategoryFactory;

  /**
   * @var CategoryCollectionFactory
   */
  protected $collectionFactory;

  /**
   * @var CategorySearchResultsInterfaceFactory
   */
  protected $searchResultsFactory;

  /**
   * @var CollectionProcessorInterface
   */
  protected $collectionProcessor;

  /**
   * @var \Magento\Framework\Api\SearchCriteriaBuilder
   */
  protected $searchCriteriaBuilder;

  /**
   * @param CategoryResource $resource
   * @param \News\Manger\Model\CategoryFactory $categoryFactory
   * @param DataCategoryFactory $dataCategoryFactory
   * @param CategoryCollectionFactory $collectionFactory
   * @param CategorySearchResultsInterfaceFactory $searchResultsFactory
   * @param CollectionProcessorInterface $collectionProcessor
   * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
   */
  public function __construct(
    CategoryResource $resource,
    \News\Manger\Model\CategoryFactory $categoryFactory,
    DataCategoryFactory $dataCategoryFactory,
    CategoryCollectionFactory $collectionFactory,
    CategorySearchResultsInterfaceFactory $searchResultsFactory,
    CollectionProcessorInterface $collectionProcessor,
    \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
  ) {
    $this->resource = $resource;
    $this->categoryFactory = $categoryFactory;
    $this->dataCategoryFactory = $dataCategoryFactory;
    $this->collectionFactory = $collectionFactory;
    $this->searchResultsFactory = $searchResultsFactory;
    $this->collectionProcessor = $collectionProcessor;
    $this->searchCriteriaBuilder = $searchCriteriaBuilder;
  }

  /**
   * @inheritDoc
   */
  public function save(CategoryInterface $category, $saveOptions = false): CategoryInterface
  {
    try {
      if (!$category instanceof \Magento\Framework\Model\AbstractModel) {
        $model = $this->categoryFactory->create();
        $model->addData($category->getData());
      } else {
        $model = $category;
      }

      // Handle array to JSON
      if (is_array($model->getParentIds())) {
        $model->setParentIds(json_encode($model->getParentIds()));
      }

      if (is_array($model->getChildIds())) {
        $model->setChildIds(json_encode($model->getChildIds()));
      }

      if (is_array($model->getNewsIds())) {
        $model->setNewsIds(json_encode($model->getNewsIds()));
      }

      $this->resource->save($model);

      return $model;
    } catch (\Exception $e) {
      throw new CouldNotSaveException(__('Could not save the category: %1', $e->getMessage()));
    }
  }

  public function getById($categoryId): CategoryInterface
  {
    $model = $this->categoryFactory->create();
    $this->resource->load($model, $categoryId);

    if (!$model->getId()) {
      throw new NoSuchEntityException(__('Category with id "%1" does not exist.', $categoryId));
    }

    // Create a new data object and populate it with the model data
    $dataObject = $this->dataCategoryFactory->create();
    $data = $model->getData();

    // Convert JSON strings to arrays for array-type fields
    $arrayFields = ['parent_ids', 'child_ids', 'news_ids'];
    foreach ($arrayFields as $field) {
      if (isset($data[$field]) && is_string($data[$field])) {
        $data[$field] = json_decode($data[$field], true) ?: [];
      }
    }

    $dataObject->setData($data);

    // Ensure we're returning a CategoryInterface
    if (!$dataObject instanceof CategoryInterface) {
      throw new \RuntimeException('Unexpected type returned from factory');
    }

    return $dataObject;
  }

  public function delete(CategoryInterface $category): bool
  {
    try {
      if (!$category instanceof \Magento\Framework\Model\AbstractModel) {
        $model = $this->categoryFactory->create();
        $this->resource->load($model, $category->getId());
      } else {
        $model = $category;
      }

      $this->resource->delete($model);
      return true;
    } catch (\Exception $e) {
      throw new CouldNotDeleteException(__('Could not delete the category: %1', $e->getMessage()));
    }
  }

  public function deleteById($categoryId): bool
  {
    $category = $this->getById($categoryId);
    return $this->delete($category);
  }

  /**
   * @inheritDoc
   */
  public function getList(?SearchCriteriaInterface $searchCriteria = null): SearchResultsInterface
  {
    $collection = $this->collectionFactory->create();

    if ($searchCriteria === null) {
      $searchCriteria = $this->searchCriteriaBuilder->create();
    }

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
  public function getChildren($categoryId)
  {
    $category = $this->getById($categoryId);
    $childIds = $category->getChildIds();

    $searchCriteria = $this->searchCriteriaBuilder
      ->addFilter('category_id', $childIds, 'in')
      ->create();

    return $this->getList($searchCriteria);
  }

  /**
   * @inheritDoc
   */
  public function getParents($categoryId)
  {
    $category = $this->getById($categoryId);
    $parentIds = $category->getParentIds();

    $searchCriteria = $this->searchCriteriaBuilder
      ->addFilter('category_id', $parentIds, 'in')
      ->create();

    return $this->getList($searchCriteria);
  }

  /**
   * @inheritDoc
   */
  public function getNews($categoryId)
  {
    // This would typically use a different repository for news items
    // For now, we'll return an empty result
    return $this->searchResultsFactory->create();
  }

  /**
   * @inheritDoc
   */
  public function addParent($categoryId, $parentId)
  {
    $category = $this->getById($categoryId);
    $parent = $this->getById($parentId);

    $parentIds = $category->getParentIds();
    if (!in_array($parentId, $parentIds)) {
      $parentIds[] = $parentId;
      $category->setParentIds($parentIds);
      $this->save($category);
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function removeParent($categoryId, $parentId)
  {
    $category = $this->getById($categoryId);
    $parentIds = $category->getParentIds();

    if (($key = array_search($parentId, $parentIds)) !== false) {
      unset($parentIds[$key]);
      $category->setParentIds(array_values($parentIds));
      $this->save($category);
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function create(CategoryInterface $category)
  {
    return $this->save($category);
  }

  /**
   * @inheritDoc
   */
  public function update($categoryId, CategoryInterface $category)
  {
    try {
      $existingCategory = $this->getById($categoryId);
      $existingCategory->addData($category->getData());
      return $this->save($existingCategory);
    } catch (\Exception $e) {
      throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
    }
  }

  /**
   * @inheritDoc
   */
  public function validate(CategoryInterface $category)
  {
    // Basic validation - can be extended as needed
    if (empty($category->getCategoryName())) {
      throw new \Magento\Framework\Exception\ValidatorException(__('Category name is required'));
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function exists($categoryId)
  {
    try {
      $this->getById($categoryId);
      return true;
    } catch (NoSuchEntityException $e) {
      return false;
    }
  }
}
