<?php

namespace News\Manger\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use News\Manger\Api\CategoryRepositoryInterface;

class UpdateCategory implements ResolverInterface
{
  /**
   * @var CategoryRepositoryInterface
   */
  private $categoryRepository;

  public function __construct(
    CategoryRepositoryInterface $categoryRepository
  ) {
    $this->categoryRepository = $categoryRepository;
  }

  /**
   * @inheritdoc
   */
  public function resolve(
    Field $field,
    $context,
    ResolveInfo $info,
    array $value = null,
    array $args = null
  ) {
    // التحقق من المدخلات الأساسية
    if (!isset($args['id']) || empty($args['input'])) {
      throw new GraphQlInputException(__('ID and input data are required.'));
    }

    $categoryId = $args['id'];
    $input = $args['input'];

    try {
      // 1. تحسين: التعامل مع NoSuchEntityException بشكل منفصل
      $category = $this->categoryRepository->getById($categoryId);
    } catch (NoSuchEntityException $e) {
      throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
    }

    // تحديث الحقول
    if (isset($input['category_name'])) {
      $category->setCategoryName($input['category_name']);
    }
    if (isset($input['category_description'])) {
      $category->setCategoryDescription($input['category_description']);
    }
    if (isset($input['category_status'])) {
      $category->setCategoryStatus($input['category_status']);
    }
    if (isset($input['parent_ids'])) {
      $category->setParentIds($input['parent_ids']);
    }

    try {
      // 2. تحسين: لا حاجة لمتغير جديد، يمكن استخدام نفس كائن category
      $this->categoryRepository->save($category);
    } catch (\Exception $e) {
      // يتم التقاط أي خطأ آخر غير متوقع أثناء الحفظ
      throw new GraphQlInputException(__('Could not update category: %1', $e->getMessage()));
    }

    // 3. تحسين: تمت إزالة 'model' => $updatedCategory
    return [
      'category_id' => $category->getCategoryId(),
      'category_name' => $category->getCategoryName(),
      'category_description' => $category->getCategoryDescription(),
      'category_status' => $category->getCategoryStatus(),
      'created_at' => $category->getCreatedAt(),
      'updated_at' => $category->getUpdatedAt(),
      'parent_ids' => $category->getParentIds(),
    ];
  }
}
