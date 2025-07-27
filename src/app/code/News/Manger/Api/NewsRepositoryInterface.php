<?php

namespace News\Manger\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use News\Manger\Api\Data\NewsInterface;

interface NewsRepositoryInterface
{
  /**
   * Save news
   *
   * @param NewsInterface $news
   * @return NewsInterface
   * @throws CouldNotSaveException
   */
  public function save(NewsInterface $news);

  /**
   * Get news by ID
   *
   * @param int $newsId
   * @return NewsInterface
   * @throws NoSuchEntityException
   */
  public function getById($newsId);

  /**
   * Get news list
   *
   * @param SearchCriteriaInterface $searchCriteria
   * @return \News\Manger\Api\Data\NewsSearchResultsInterface
   */
  public function getList(SearchCriteriaInterface $searchCriteria);

  /**
   * Delete news
   *
   * @param NewsInterface $news
   * @return bool
   * @throws CouldNotDeleteException
   */
  public function delete(NewsInterface $news);

  /**
   * Delete news by ID
   *
   * @param int $newsId
   * @return bool
   * @throws CouldNotDeleteException
   * @throws NoSuchEntityException
   */
  public function deleteById($newsId);

  /**
   * Get categories for news
   *
   * @param int $newsId
   * @return \News\Manger\Api\Data\CategorySearchResultsInterface
   */
  public function getCategories($newsId);

  /**
   * Add category to news
   *
   * @param int $newsId
   * @param int $categoryId
   * @return bool
   */
  public function addCategory($newsId, $categoryId);

  /**
   * Remove category from news
   *
   * @param int $newsId
   * @param int $categoryId
   * @return bool
   */
  public function removeCategory($newsId, $categoryId);

  /**
   * Set categories for news (replaces existing categories)
   *
   * @param int $newsId
   * @param int[] $categoryIds
   * @return bool
   */
  public function setCategories($newsId, array $categoryIds);
}
