<?php

namespace News\Manger\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use News\Manger\Model\CategoryFactory;

class Save extends \Magento\Backend\App\Action
{
  const ADMIN_RESOURCE = 'News_Manger::category_save';
  const PAGE_TITLE = 'Save Category';

  /**
   * @var DataPersistorInterface
   */
  protected $dataPersistor;

  /**
   * @var CategoryFactory
   */
  protected $categoryFactory;

  /**
   * @var DateFilter
   */
  protected $dateFilter;

  /**
   * @param Context $context
   * @param DataPersistorInterface $dataPersistor
   * @param CategoryFactory $categoryFactory
   * @param DateFilter $dateFilter
   */
  public function __construct(
    Context $context,
    DataPersistorInterface $dataPersistor,
    CategoryFactory $categoryFactory,
    DateFilter $dateFilter
  ) {
    $this->dataPersistor = $dataPersistor;
    $this->categoryFactory = $categoryFactory;
    $this->dateFilter = $dateFilter;
    parent::__construct($context);
  }

  /**
   * Save action
   *
   * @return \Magento\Framework\Controller\ResultInterface
   */
  public function execute()
  {
    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
    $resultRedirect = $this->resultRedirectFactory->create();
    $data = $this->getRequest()->getPostValue();

    if ($data) {
      $id = $this->getRequest()->getParam('category_id');

      // Initialize model
      $model = $this->categoryFactory->create();
      if ($id) {
        $model->load($id);
        if (!$model->getId()) {
          $this->messageManager->addErrorMessage(__('This category no longer exists.'));
          return $resultRedirect->setPath('*/*/');
        }
      }

      // Validate required fields
      if (empty($data['category_name'])) {
        $this->messageManager->addErrorMessage(__('Please provide the category name.'));
        return $this->redirectWithData($resultRedirect, $data, $id);
      }

      if (empty($data['category_description'])) {
        $this->messageManager->addErrorMessage(__('Please provide the category description.'));
        return $this->redirectWithData($resultRedirect, $data, $id);
      }

      if (!isset($data['category_status']) || $data['category_status'] === '') {
        $this->messageManager->addErrorMessage(__('Please select the category status.'));
        return $this->redirectWithData($resultRedirect, $data, $id);
      }

      // Prepare data
      if (isset($data['created_at']) && $data['created_at']) {
        try {
          $data['created_at'] = $this->dateFilter->filter($data['created_at']);
        } catch (\Exception $e) {
          $data['created_at'] = null;
        }
      } else {
        if (!$id) { // Only set created_at for new records
          $data['created_at'] = date('Y-m-d H:i:s');
        }
      }

      // Always set updated_at
      $data['updated_at'] = date('Y-m-d H:i:s');

      // Handle parent category
      if (isset($data['category_parent']) && $data['category_parent'] === '') {
        $data['category_parent'] = null;
      }

      // Remove form_key from data
      unset($data['form_key']);

      $model->setData($data);

      try {
        $model->save();
        $this->messageManager->addSuccessMessage(__('The category has been saved.'));
        $this->dataPersistor->clear('news_category');

        if ($this->getRequest()->getParam('back')) {
          return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
      } catch (LocalizedException $e) {
        $this->messageManager->addErrorMessage($e->getMessage());
      } catch (\Exception $e) {
        $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
      }

      $this->dataPersistor->set('news_category', $data);
      return $this->redirectWithData($resultRedirect, $data, $id);
    }

    return $resultRedirect->setPath('*/*/');
  }

  /**
   * Redirect with form data
   *
   * @param \Magento\Framework\Controller\ResultInterface $resultRedirect
   * @param array $data
   * @param int|null $id
   * @return \Magento\Framework\Controller\ResultInterface
   */
  private function redirectWithData($resultRedirect, $data, $id)
  {
    $this->dataPersistor->set('news_category', $data);
    if ($id) {
      return $resultRedirect->setPath('*/*/edit', ['category_id' => $id]);
    }
    return $resultRedirect->setPath('*/*/new');
  }
}
