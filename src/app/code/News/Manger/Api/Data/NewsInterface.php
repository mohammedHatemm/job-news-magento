<?php

namespace News\Manger\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface NewsInterface extends ExtensibleDataInterface
{
  const NEWS_ID = 'news_id';
  const TITLE = 'news_title';
  const CONTENT = 'news_content';
  const STATUS = 'news_status';
  const CREATED_AT = 'created_at';
  const UPDATED_AT = 'updated_at';
  const CATEGORY_IDS = 'category_ids';

  /**
   * Get news ID
   *
   * @return int|null
   */
  public function getId();

  /**
   * Set news ID
   *
   * @param int $id
   * @return $this
   */
  public function setId($id);

  /**
   * Get news title
   *
   * @return string
   */
  public function getTitle();

  /**
   * Set news title
   *
   * @param string $title
   * @return $this
   */
  public function setTitle($title);

  /**
   * Get news content
   *
   * @return string
   */
  public function getContent();

  /**
   * Set news content
   *
   * @param string $content
   * @return $this
   */
  public function setContent($content);

  /**
   * Get status
   *
   * @return int
   */
  public function getStatus();

  /**
   * Set status
   *
   * @param int $status
   * @return $this
   */
  public function setStatus($status);

  /**
   * Get creation time
   *
   * @return string
   */
  public function getCreatedAt();

  /**
   * Get update time
   *
   * @return string
   */
  public function getUpdatedAt();

  /**
   * Get associated category IDs
   *
   * @return int[]
   */
  public function getCategoryIds();

  /**
   * Set associated category IDs
   *
   * @param int[] $categoryIds
   * @return $this
   */
  public function setCategoryIds(array $categoryIds);
}
