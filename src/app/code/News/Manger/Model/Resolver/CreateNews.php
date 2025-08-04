<?php

namespace News\Manger\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use News\Manger\Api\NewsRepositoryInterface;
use News\Manger\Api\Data\NewsInterfaceFactory;

class CreateNews implements ResolverInterface
{
  /**
   * @var NewsRepositoryInterface
   */
  private $newsRepository;

  /**
   * @var NewsInterfaceFactory
   */
  private $newsFactory;

  public function __construct(
    NewsRepositoryInterface $newsRepository,
    NewsInterfaceFactory $newsFactory
  ) {
    $this->newsRepository = $newsRepository;
    $this->newsFactory = $newsFactory;
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
    if (!isset($args['input'])) {
      throw new GraphQlInputException(__('Input data is required'));
    }

    $input = $args['input'];

    if (empty($input['news_title'])) {
      throw new GraphQlInputException(__('News title is required'));
    }

    if (empty($input['news_content'])) {
      throw new GraphQlInputException(__('News content is required'));
    }

    try {
      $news = $this->newsFactory->create();
      $news->setNewsTitle($input['news_title']);
      $news->setNewsContent($input['news_content']);
      $news->setNewsStatus($input['news_status'] ?? 1);

      if (isset($input['category_ids'])) {
        $news->setCategoryIds($input['category_ids']);
      }

      $savedNews = $this->newsRepository->save($news);

      return [
        'news_id' => $savedNews->getNewsId(),
        'news_title' => $savedNews->getNewsTitle(),
        'news_content' => $savedNews->getNewsContent(),
        'news_status' => $savedNews->getNewsStatus(),
        'created_at' => $savedNews->getCreatedAt(),
        'updated_at' => $savedNews->getUpdatedAt(),
        'category_ids' => $savedNews->getCategoryIds(),
        'model' => $savedNews
      ];
    } catch (\Exception $e) {
      throw new GraphQlInputException(__('Could not create news: %1', $e->getMessage()));
    }
  }
}
