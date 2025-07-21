<?php

namespace News\Manger\Block\User\News;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use News\Manger\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class View extends Template
{
  protected $coreRegistry;
  protected $categoryCollectionFactory;
  protected $resourceConnection;
  protected $logger;

  private $allCategories;

  public function __construct(
    Context $context,
    Registry $registry,
    CategoryCollectionFactory $categoryCollectionFactory,
    ResourceConnection $resourceConnection,
    LoggerInterface $logger,
    array $data = []
  ) {
    $this->coreRegistry = $registry;
    $this->categoryCollectionFactory = $categoryCollectionFactory;
    $this->resourceConnection = $resourceConnection;
    $this->logger = $logger;
    parent::__construct($context, $data);
  }

  /**
   * جلب الخبر الحالي
   */
  public function getNews()
  {
    return $this->coreRegistry->registry('current_news');
  }

  /**
   * جلب التسلسل الهرمي المفصل لجميع التصنيفات المرتبطة بالخبر الحالي
   * @return array
   */
  public function getDetailedCategoryPathsForCurrentNews(): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    $this->loadAllCategoriesOnce();
    $categoryIds = $this->getCategoryIdsForNews($news->getId());

    $paths = [];
    foreach ($categoryIds as $categoryId) {
      $pathData = $this->buildDetailedCategoryPath($categoryId);
      if (!empty($pathData)) {
        $paths[] = $pathData;
      }
    }
    return $paths;
  }

  /**
   * جلب جميع معلومات الخبر المفصلة
   * @return array
   */
  public function getDetailedNewsInfo(): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    return [
      'id' => $news->getId(),
      'title' => $news->getNewsTitle(),
      'content' => $news->getNewsContent(),
      'status' => $news->getNewsStatus(),
      'created_at' => $news->getCreatedAt(),
      'updated_at' => $news->getUpdatedAt(),
      'author_name' => $news->getAuthorName() ?? __('Unknown Author'),
      'meta_description' => $news->getMetaDescription() ?? '',
      'meta_keywords' => $news->getMetaKeywords() ?? '',
      'featured_image' => $news->getFeaturedImage() ?? '',
      'excerpt' => $news->getExcerpt() ?? '',
      'view_count' => $news->getViewCount() ?? 0,
      'slug' => $news->getSlug() ?? '',
      'is_featured' => $news->getIsFeatured() ?? 0,
      'publish_date' => $news->getPublishDate() ?? $news->getCreatedAt(),
    ];
  }

  /**
   * جلب إحصائيات الخبر
   * @return array
   */
  public function getNewsStatistics(): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    return [
      'word_count' => str_word_count(strip_tags($news->getNewsContent())),
      'reading_time' => $this->calculateReadingTime($news->getNewsContent()),
      'character_count' => strlen(strip_tags($news->getNewsContent())),
      'paragraph_count' => substr_count($news->getNewsContent(), '</p>'),
    ];
  }

  /**
   * حساب وقت القراءة التقريبي
   * @param string $content
   * @return int
   */
  private function calculateReadingTime($content): int
  {
    $wordCount = str_word_count(strip_tags($content));
    $readingSpeed = 200; // كلمة في الدقيقة
    return max(1, ceil($wordCount / $readingSpeed));
  }

  /**
   * جلب الأخبار ذات الصلة (نفس الفئات)
   * @return array
   */
  public function getRelatedNews($limit = 5): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    try {
      $connection = $this->resourceConnection->getConnection();

      // جلب الأخبار التي تشارك نفس الفئات
      $select = $connection->select()
        ->from(['n' => $this->resourceConnection->getTableName('news_news')], [
          'id',
          'news_title',
          'excerpt',
          'created_at',
          'slug',
          'featured_image'
        ])
        ->join(
          ['nc' => $this->resourceConnection->getTableName('news_news_category')],
          'n.id = nc.news_id',
          []
        )
        ->where('nc.category_id IN (?)', $this->getCategoryIdsForNews($news->getId()))
        ->where('n.id != ?', $news->getId())
        ->where('n.news_status = ?', 1)
        ->group('n.id')
        ->order('n.created_at DESC')
        ->limit($limit);

      return $connection->fetchAll($select);
    } catch (\Exception $e) {
      $this->logger->error('Error getting related news: ' . $e->getMessage());
      return [];
    }
  }

  private function getCategoryIdsForNews($newsId)
  {
    try {
      $connection = $this->resourceConnection->getConnection();
      $select = $connection->select()
        ->from($this->resourceConnection->getTableName('news_news_category'), ['category_id'])
        ->where('news_id = ?', $newsId);
      return $connection->fetchCol($select);
    } catch (\Exception $e) {
      $this->logger->error('Error getting category IDs for news: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * بناء التسلسل الهرمي المفصل للفئة
   * @param int $categoryId
   * @return array
   */
  private function buildDetailedCategoryPath(int $categoryId): array
  {
    if (!isset($this->allCategories[$categoryId])) {
      return [];
    }

    $pathItems = [];
    $currentId = $categoryId;
    $level = 0;

    while ($currentId && isset($this->allCategories[$currentId])) {
      $category = $this->allCategories[$currentId];
      array_unshift($pathItems, [
        'id' => $currentId,
        'name' => $category['category_name'],
        'level' => $level,
        'url' => $this->getUrl('newsuser/category/view', ['id' => $currentId])
      ]);
      $currentId = $category['parent_id'];
      $level++;
    }

    return [
      'path_string' => implode(' / ', array_column($pathItems, 'name')),
      'path_items' => $pathItems,
      'depth' => count($pathItems),
      'leaf_category' => end($pathItems)
    ];
  }

  private function loadAllCategoriesOnce(): void
  {
    if ($this->allCategories === null) {
      $this->allCategories = [];
      try {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToSelect(['id', 'category_name', 'parent_id', 'category_description', 'sort_order']);

        foreach ($collection as $category) {
          $this->allCategories[$category->getId()] = [
            'category_name' => $category->getCategoryName(),
            'parent_id' => $category->getParentId(),
            'category_description' => $category->getCategoryDescription(),
            'sort_order' => $category->getSortOrder()
          ];
        }
      } catch (\Exception $e) {
        $this->logger->error('Error loading all categories: ' . $e->getMessage());
      }
    }
  }

  /**
   * تنسيق التاريخ
   * @param string $date
   * @return string
   */
  public function formatArabicDate($date): string
  {
    if (!$date) {
      return '';
    }

    $timestamp = strtotime($date);
    $arabicMonths = [
      1 => 'يناير',
      2 => 'فبراير',
      3 => 'مارس',
      4 => 'أبريل',
      5 => 'مايو',
      6 => 'يونيو',
      7 => 'يوليو',
      8 => 'أغسطس',
      9 => 'سبتمبر',
      10 => 'أكتوبر',
      11 => 'نوفمبر',
      12 => 'ديسمبر'
    ];

    $day = date('d', $timestamp);
    $month = $arabicMonths[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    $time = date('H:i', $timestamp);

    return "{$day} {$month} {$year} - {$time}";
  }

  /**
   * جلب رابط المشاركة
   * @return array
   */
  public function getSharingLinks(): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    $currentUrl = urlencode($this->getUrl('*/*/*', ['_current' => true]));
    $title = urlencode($news->getNewsTitle());

    return [
      'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$currentUrl}",
      'twitter' => "https://twitter.com/intent/tweet?url={$currentUrl}&text={$title}",
      'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$currentUrl}",
      'whatsapp' => "https://wa.me/?text={$title}%20{$currentUrl}",
      'telegram' => "https://t.me/share/url?url={$currentUrl}&text={$title}"
    ];
  }
}
