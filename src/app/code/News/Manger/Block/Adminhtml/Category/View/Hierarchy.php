<?php

namespace News\Manger\Block\Adminhtml\Category\View;

use Magento\Backend\Block\Template;
use News\Manger\Model\CategoryFactory;
use News\Manger\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Hierarchy extends Template
{
  protected $categoryFactory;
  protected $collectionFactory;
  protected $registry;
  protected $logger;

  public function __construct(
    Template\Context $context,
    CategoryFactory $categoryFactory,
    CollectionFactory $collectionFactory,
    Registry $registry,
    LoggerInterface $logger,
    array $data = []
  ) {
    $this->categoryFactory = $categoryFactory;
    $this->collectionFactory = $collectionFactory;
    $this->registry = $registry;
    $this->logger = $logger;
    parent::__construct($context, $data);
  }

  /**
   * الحصول على الفئة الحالية
   */
  public function getCurrentCategory()
  {
    return $this->registry->registry('news_category');
  }

  /**
   * التحقق من وجود فئة
   */
  public function hasCategory()
  {
    return $this->getCurrentCategory() && $this->getCurrentCategory()->getId();
  }

  /**
   * الحصول على مسارات Breadcrumb
   */
  public function getBreadcrumbPaths()
  {
    if (!$this->hasCategory()) {
      return [];
    }

    $category = $this->getCurrentCategory();
    return $category->getBreadcrumbPaths();
  }

  /**
   * بناء بيانات الشجرة الهرمية للقائمة
   */
  public function getCategoryTreeData()
  {
    try {
      $collection = $this->collectionFactory->create()->addOrder('category_name', 'ASC');
      $categoryMap = [];
      $childrenMap = [];

      // بناء خريطة الفئات
      foreach ($collection as $category) {
        $categoryId = $category->getId();
        $parentIds = $category->getData('parent_ids');

        // التعامل مع parent_ids كـ JSON أو string
        if (is_string($parentIds)) {
          $parentIds = json_decode($parentIds ?: '[]', true);
        }
        if (!is_array($parentIds)) {
          $parentIds = [];
        }

        $categoryData = [
          'id' => $categoryId,
          'name' => $category->getCategoryName() ?: 'Unnamed Category',
          'description' => $category->getCategoryDescription() ?: '',
          'level' => 0, // سنحسبه لاحقاً
          'is_current' => $this->isCurrentCategory($categoryId),
          'view_url' => $this->getViewUrl($categoryId),
          'edit_url' => $this->getEditUrl($categoryId),
          'news_count' => $this->getNewsCount($categoryId),
          'children' => []
        ];

        $categoryMap[$categoryId] = $categoryData;

        // تصنيف الأطفال
        if (empty($parentIds)) {
          $childrenMap[0][] = $categoryId;
        } else {
          foreach ($parentIds as $parentId) {
            if ($parentId) {
              $childrenMap[$parentId][] = $categoryId;
            }
          }
        }
      }

      // بناء الشجرة
      return $this->buildTree($categoryMap, $childrenMap, 0);
    } catch (\Exception $e) {
      $this->logger->error('Error building category tree: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * بناء الشجرة الهرمية
   */
  private function buildTree($categoryMap, $childrenMap, $parentId = 0)
  {
    $tree = [];

    if (!isset($childrenMap[$parentId])) {
      return $tree;
    }

    foreach ($childrenMap[$parentId] as $categoryId) {
      if (isset($categoryMap[$categoryId])) {
        $category = $categoryMap[$categoryId];

        // إضافة الأطفال
        $category['children'] = $this->buildTree($categoryMap, $childrenMap, $categoryId);

        $tree[] = $category;
      }
    }

    return $tree;
  }

  /**
   * حساب مستوى الفئة
   */
  private function calculateLevel($category, $collection)
  {
    $parentIds = json_decode($category->getData('parent_ids') ?: '[]', true);

    if (empty($parentIds)) {
      return 0;
    }

    $maxLevel = 0;
    foreach ($parentIds as $parentId) {
      foreach ($collection as $parentCategory) {
        if ($parentCategory->getId() == $parentId) {
          $parentLevel = $this->calculateLevel($parentCategory, $collection);
          $maxLevel = max($maxLevel, $parentLevel);
          break;
        }
      }
    }

    return $maxLevel + 1;
  }

  /**
   * التحقق من كون الفئة هي الحالية
   */
  private function isCurrentCategory($categoryId)
  {
    $currentCategory = $this->getCurrentCategory();
    return $currentCategory && $currentCategory->getId() == $categoryId;
  }

  /**
   * رابط عرض الفئة
   */
  private function getViewUrl($categoryId)
  {
    return $this->getUrl('news/category/view', ['id' => $categoryId]);
  }

  /**
   * رابط تعديل الفئة
   */
  private function getEditUrl($categoryId)
  {
    return $this->getUrl('news/category/edit', ['id' => $categoryId]);
  }

  /**
   * عدد الأخبار في الفئة
   */
  private function getNewsCount($categoryId)
  {
    try {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();

      $tableName = $connection->getTableName('news_news_category');
      $select = $connection->select()
        ->from($tableName, [new \Zend_Db_Expr('COUNT(*)')])
        ->where('category_id = ?', $categoryId);

      return (int) $connection->fetchOne($select);
    } catch (\Exception $e) {
      $this->logger->error('Error getting news count for category ' . $categoryId . ': ' . $e->getMessage());
      return 0;
    }
  }

  /**
   * الحصول على شجرة كاملة مع التوسع التلقائي للمسار الحالي
   */
  public function getExpandedTreeData()
  {
    $treeData = $this->getCategoryTreeData();

    if ($this->hasCategory()) {
      $currentId = $this->getCurrentCategory()->getId();
      $this->markExpandedPath($treeData, $currentId);
    }

    return $treeData;
  }

  /**
   * تحديد المسار للتوسع التلقائي
   */
  private function markExpandedPath(&$tree, $targetId, $found = false)
  {
    foreach ($tree as &$category) {
      if ($category['id'] == $targetId) {
        $category['expanded'] = true;
        return true;
      }

      if (!empty($category['children'])) {
        if ($this->markExpandedPath($category['children'], $targetId)) {
          $category['expanded'] = true;
          return true;
        }
      }
    }

    return false;
  }

  /**
   * الحصول على إحصائيات الشجرة
   */
  public function getTreeStats()
  {
    $treeData = $this->getCategoryTreeData();

    return [
      'total_categories' => $this->countCategories($treeData),
      'root_categories' => count($treeData),
      'max_depth' => $this->getMaxDepth($treeData),
      'categories_with_news' => $this->countCategoriesWithNews($treeData)
    ];
  }

  private function countCategories($tree)
  {
    $count = count($tree);
    foreach ($tree as $category) {
      if (!empty($category['children'])) {
        $count += $this->countCategories($category['children']);
      }
    }
    return $count;
  }

  private function getMaxDepth($tree, $currentDepth = 0)
  {
    $maxDepth = $currentDepth;
    foreach ($tree as $category) {
      if (!empty($category['children'])) {
        $depth = $this->getMaxDepth($category['children'], $currentDepth + 1);
        $maxDepth = max($maxDepth, $depth);
      }
    }
    return $maxDepth;
  }

  private function countCategoriesWithNews($tree)
  {
    $count = 0;
    foreach ($tree as $category) {
      if ($category['news_count'] > 0) {
        $count++;
      }
      if (!empty($category['children'])) {
        $count += $this->countCategoriesWithNews($category['children']);
      }
    }
    return $count;
  }
}
