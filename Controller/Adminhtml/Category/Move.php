<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Move category admin controller
 */
class Move extends \Magefan\Blog\Controller\Adminhtml\Category implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $dataPersistor);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->logger = $logger;
    }

    /**
     * Move category action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /**
         * New parent category identifier
         */
        $parentNodeId = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId = $this->getRequest()->getPost('aid', false);

        /** @var $block \Magento\Framework\View\Element\Messages */
        $block = $this->layoutFactory->create()->getMessagesBlock();
        $error = false;

        try {
            $categoryId = (int)$this->getRequest()->getPost('id');

            if ($categoryId) {
                $this->getRequest()->setParam('id', $categoryId);

                $category = $this->_getModel();

                if ($category === false) {
                    throw new \Exception(__('Category is not available for requested store.'));
                }

                $category->move($parentNodeId, $prevNodeId);
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addErrorMessage(__('There was a category move error.'));
            $this->logger->critical($e);
        }

        if (!$error) {
            $this->messageManager->addSuccessMessage(__('You moved the category.'));
        }

        $block->setMessages($this->messageManager->getMessages(true));
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'messages' => $block->getGroupedHtml(),
            'error' => $error
        ]);
    }
}
