<?php

namespace News\Manger\Block\User\News;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use News\Manger\Model\Category;
use News\Manger\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class View extends Template
{
  protected $coreRegistry;
  protected $categoryCollectionFactory;
  protected $resourceConnection;
  protected $logger;

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

  public function getNews()
  {
    return $this->coreRegistry->registry('current_news');
  }

  public function getDetailedCategoryPathsForCurrentNews(): array
  {
    $news = $this->getNews();
    if (!$news) {
      $this->logger->debug('No News Found');
      return [];
    }

    $categoryIds = $this->getCategoryIdsForNews($news->getId());
    $this->logger->debug('Category IDs for News ' . $news->getId() . ': ' . json_encode($categoryIds));

    $paths = [];
    foreach ($categoryIds as $categoryId) {
      $category = $this->categoryCollectionFactory->create()->addFieldToFilter('category_id', $categoryId)->getFirstItem();
      if ($category->getId()) {
        $breadcrumbPaths = $category->getBreadcrumbPaths();
        foreach ($breadcrumbPaths as $breadcrumb) {
          $paths[] = [
            'name' => implode(' > ', array_column($breadcrumb, 'name')),
            'url' => $this->getUrl('newsuser/category/view', ['id' => $categoryId]),
            'level' => $category->getLevel()
          ];
        }
      }
    }
    $this->logger->debug('Category Paths for News ' . $news->getId() . ': ' . json_encode($paths));

    return $paths;
  }

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

  private function calculateReadingTime($content): int
  {
    $wordCount = str_word_count(strip_tags($content));
    $readingSpeed = 200; // كلمة في الدقيقة
    return max(1, ceil($wordCount / $readingSpeed));
  }

  public function getRelatedNews($limit = 5): array
  {
    $news = $this->getNews();
    if (!$news) {
      return [];
    }

    try {
      $connection = $this->resourceConnection->getConnection();

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
