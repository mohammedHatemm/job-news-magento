<?php

namespace News\Manger\Controller\Adminhtml\News;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use News\Manger\Model\NewsFactory;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'News_Manger::news_save';
    const PAGE_TITLE = 'Save News';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var NewsFactory
     */
    protected $newsFactory;

    /**
     * @var DateFilter
     */
    protected $dateFilter;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param NewsFactory $newsFactory
     * @param DateFilter $dateFilter
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        NewsFactory $newsFactory,
        DateFilter $dateFilter
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->newsFactory = $newsFactory;
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
            if (empty($data['title'])) {
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

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('The news has been saved.'));
                $this->dataPersistor->clear('news_news');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['news_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the news.'));
            }

            $this->dataPersistor->set('news_news', $data);
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
        $this->dataPersistor->set('news_news', $data);
        if ($id) {
            return $resultRedirect->setPath('*/*/edit', ['news_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/new');
    }

    /**
     * Is the user allowed to view the page.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
