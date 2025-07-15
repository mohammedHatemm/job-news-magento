<?php

namespace News\Manger\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use News\Manger\Model\CategoryFactory;
use Psr\Log\LoggerInterface;

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
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @param Context $context
   * @param DataPersistorInterface $dataPersistor
   * @param CategoryFactory $categoryFactory
   * @param LoggerInterface $logger
   */
  public function __construct(
    Context $context,
    DataPersistorInterface $dataPersistor,
    CategoryFactory $categoryFactory,
    LoggerInterface $logger
  ) {
    $this->dataPersistor = $dataPersistor;
    $this->categoryFactory = $categoryFactory;
    $this->logger = $logger;
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
      $validationErrors = $this->validateData($data);
      if (!empty($validationErrors)) {
        foreach ($validationErrors as $error) {
          $this->messageManager->addErrorMessage($error);
        }
        return $this->redirectWithData($resultRedirect, $data, $id);
      }

      // Prepare data
      $data = $this->prepareData($data, $model);

      // Check for parent category circular reference
      if (isset($data['parent_id']) && $data['parent_id'] && $id) {
        if ($this->hasCircularReference($data['parent_id'], $id)) {
          $this->messageManager->addErrorMessage(__('Cannot set parent category: This would create a circular reference.'));
          return $this->redirectWithData($resultRedirect, $data, $id);
        }
      }

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
        $this->logger->error('LocalizedException while saving category: ' . $e->getMessage());
      } catch (\Exception $e) {
        $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
        $this->logger->error('Exception while saving category: ' . $e->getMessage());
      }

      $this->dataPersistor->set('news_category', $data);
      return $this->redirectWithData($resultRedirect, $data, $id);
    }

    return $resultRedirect->setPath('*/*/');
  }

  /**
   * Validate form data
   *
   * @param array $data
   * @return array
   */
  private function validateData($data)
  {
    $errors = [];

    if (empty($data['category_name']) || trim($data['category_name']) === '') {
      $errors[] = __('Please provide the category name.');
    }

    if (empty($data['category_description']) || trim($data['category_description']) === '') {
      $errors[] = __('Please provide the category description.');
    }

    if (!isset($data['category_status']) || $data['category_status'] === '') {
      $errors[] = __('Please select the category status.');
    }

    // Validate parent_id if provided
    if (isset($data['parent_id']) && $data['parent_id'] !== '' && !is_numeric($data['parent_id'])) {
      $errors[] = __('Parent category ID must be numeric.');
    }

    return $errors;
  }

  /**
   * Prepare data for saving
   *
   * @param array $data
   * @param \News\Manger\Model\Category $model
   * @return array
   */
  private function prepareData($data, $model)
  {
    // Handle parent category
    if (isset($data['parent_id']) && $data['parent_id'] === '') {
      $data['parent_id'] = null;
    }

    // Set timestamps
    if (!$model->getId()) {
      // New record
      $data['created_at'] = date('Y-m-d H:i:s');
    }
    $data['updated_at'] = date('Y-m-d H:i:s');

    // Remove unnecessary fields
    unset($data['form_key']);
    unset($data['created_at_display']);
    unset($data['updated_at_display']);

    // Trim string values
    $data['category_name'] = trim($data['category_name']);
    $data['category_description'] = trim($data['category_description']);

    return $data;
  }

  /**
   * Check for circular reference in parent-child relationship
   *
   * @param int $parentId
   * @param int $categoryId
   * @return bool
   */
  private function hasCircularReference($parentId, $categoryId)
  {
    if ($parentId == $categoryId) {
      return true;
    }

    try {
      $parentModel = $this->categoryFactory->create()->load($parentId);
      if ($parentModel->getId() && $parentModel->getParentId()) {
        return $this->hasCircularReference($parentModel->getParentId(), $categoryId);
      }
    } catch (\Exception $e) {
      $this->logger->error('Error checking circular reference: ' . $e->getMessage());
      return false;
    }

    return false;
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
