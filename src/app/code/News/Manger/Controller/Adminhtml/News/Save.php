<?php

namespace News\Manger\Controller\Adminhtml\News;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use News\Manger\Model\NewsFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    const ADMIN_RESOURCE = 'News_Manger::news_save';
    const PAGE_TITLE = 'Save News';

    protected $dataPersistor;
    protected $newsFactory;
    protected $dateFilter;
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        NewsFactory $newsFactory,
        DateFilter $dateFilter,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->newsFactory = $newsFactory;
        $this->dateFilter = $dateFilter;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        // Log POST data for debugging
        $this->logger->info('POST Data: ' . json_encode($data));

        if ($data) {
            $id = $this->getRequest()->getParam('news_id');

            // Initialize model
            $model = $this->newsFactory->create();
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This news no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            // Validate required fields
            if (empty($data['news_title'])) {
                $this->messageManager->addErrorMessage(__('Please provide the news title.'));
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
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            // Extract category_ids and remove from model data
            $categoryIds = isset($data['category_ids']) && is_array($data['category_ids']) ? $data['category_ids'] : [];
            unset($data['category_ids']);

            $model->setData($data);

            try {
                $model->save();

                // Save category associations
                $this->saveCategoryAssociations($model->getId(), $categoryIds);

                $this->messageManager->addSuccessMessage(__('The news has been saved.'));
                $this->dataPersistor->clear('news_news');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['news_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->error('Error saving news: ' . $e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the news.'));
            }

            $data['category_ids'] = $categoryIds;
            $this->dataPersistor->set('news_news', $data);
            return $this->redirectWithData($resultRedirect, $data, $id);
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function saveCategoryAssociations($newsId, $categoryIds)
    {
        $this->logger->info('Saving categories for news ' . $newsId . ': ' . json_encode($categoryIds));
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('news_news_category');

        // Validate category IDs
        $validCategoryIds = [];
        if (!empty($categoryIds)) {
            $validCategoryIds = $connection->fetchCol(
                "SELECT category_id FROM news_category WHERE category_id IN (?)",
                $categoryIds
            );
            if (empty($validCategoryIds)) {
                $this->logger->info('No valid category IDs found for news ' . $newsId);
            }
        }

        // Delete existing associations
        try {
            $connection->delete($table, ['news_id = ?' => $newsId]);
            $this->logger->info('Deleted existing associations for news ' . $newsId);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting existing category associations: ' . $e->getMessage());
            throw $e;
        }

        // Insert new associations
        if (!empty($validCategoryIds)) {
            $data = [];
            foreach ($validCategoryIds as $categoryId) {
                if (!empty($categoryId)) {
                    $data[] = [
                        'news_id' => (int)$newsId,
                        'category_id' => (int)$categoryId
                    ];
                }
            }
            if (!empty($data)) {
                try {
                    $connection->insertMultiple($table, $data);
                    $this->logger->info('Inserted new category associations for news ' . $newsId);
                } catch (\Exception $e) {
                    $this->logger->error('Error inserting category associations: ' . $e->getMessage());
                    throw $e;
                }
            }
        }
    }

    private function redirectWithData($resultRedirect, $data, $id)
    {
        $this->dataPersistor->set('news_news', $data);
        if ($id) {
            return $resultRedirect->setPath('*/*/edit', ['news_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/new');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
