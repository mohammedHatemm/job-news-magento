<?php

namespace News\Manger\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for category data.
 *
 * @api
 */
interface CategoryInterface extends ExtensibleDataInterface
{
  const CATEGORY_ID = 'category_id';
  const NAME = 'category_name';
  const DESCRIPTION = 'category_description';
  const STATUS = 'category_status';
  const CREATED_AT = 'created_at';
  const UPDATED_AT = 'updated_at';
  const PARENT_IDS = 'parent_ids';
  const CHILD_IDS = 'child_ids';
  const NEWS_IDS = 'news_ids';

  /**
   * Get category ID.
   *
   * @return int|null
   */
  public function getCategoryId();

  /**
   * Set category ID.
   *
   * @param int $id
   * @return $this
   */
  public function setCategoryId($id);

  /**
   * Get category name.
   *
   * @return string
   */
  public function getCategoryName();

  /**
   * Set category name.
   *
   * @param string $name
   * @return $this
   */
  public function setCategoryName($name);

  /**
   * Get category description.
   *
   * @return string|null
   */
  public function getCategoryDescription();

  /**
   * Set category description.
   *
   * @param string $description
   * @return $this
   */
  public function setCategoryDescription($description);

  /**
   * Get category status.
   *
   * @return int
   */
  public function getCategoryStatus();

  /**
   * Set category status.
   *
   * @param int $status
   * @return $this
   */
  public function setCategoryStatus($status);

  /**
   * Get creation time.
   *
   * @return string|null
   */
  public function getCreatedAt();

  /**
   * Set creation time.
   *
   * @param string $createdAt
   * @return $this
   */
  public function setCreatedAt($createdAt);

  /**
   * Get update time.
   *
   * @return string|null
   */
  public function getUpdatedAt();

  /**
   * Set update time.
   *
   * @param string $updatedAt
   * @return $this
   */
  public function setUpdatedAt($updatedAt);

  /**
   * Get parent category IDs.
   *
   * @return int[]
   */
  public function getParentIds();

  /**
   * Set parent category IDs.
   *
   * @param int[] $parentIds
   * @return $this
   */
  public function setParentIds($parentIds);

  /**
   * Get child category IDs.
   *
   * @return int[]
   */
  public function getChildIds();

  /**
   * Set child category IDs.
   *
   * @param int[] $childIds
   * @return $this
   */
  public function setChildIds($childIds);

  /**
   * Get news IDs.
   *
   * @return int[]
   */
  public function getNewsIds();

  /**
   * Set news IDs.
   *
   * @param int[] $newsIds
   * @return $this
   */
  public function setNewsIds($newsIds);
}
