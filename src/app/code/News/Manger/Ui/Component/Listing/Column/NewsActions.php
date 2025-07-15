<?php

namespace News\Manger\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CategoryActions extends Column
{
  const URL_PATH_EDIT = 'news/category/edit';
  const URL_PATH_DELETE = 'news/category/delete'; // لازم تكون عامل Controller لـ Delete

  protected $urlBuilder;

  public function __construct(
    ContextInterface $context,
    UiComponentFactory $uiComponentFactory,
    UrlInterface $urlBuilder,
    array $components = [],
    array $data = []
  ) {
    $this->urlBuilder = $urlBuilder;
    parent::__construct($context, $uiComponentFactory, $components, $data);
  }

  public function prepareDataSource(array $dataSource)
  {
    if (isset($dataSource['data']['items'])) {
      foreach ($dataSource['data']['items'] as &$item) {
        if (isset($item['category_id'])) {
          $item[$this->getData('name')] = [
            'edit' => [
              'href' => $this->urlBuilder->getUrl(
                self::URL_PATH_EDIT,
                ['category_id' => $item['category_id']]
              ),
              'label' => __('Edit')
            ],
            'delete' => [
              'href' => $this->urlBuilder->getUrl(
                self::URL_PATH_DELETE,
                ['category_id' => $item['category_id']]
              ),
              'label' => __('Delete'),
              'confirm' => [
                'title' => __('Delete Category'),
                'message' => __('Are you sure you want to delete this category?')
              ]
            ]
          ];
        }
      }
    }

    return $dataSource;
  }
}
