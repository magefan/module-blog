<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magefan\Blog\Controller\Adminhtml\Comment;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\BlockRepositoryInterface as BlockRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cms\Api\Data\BlockInterface;

class InlineEdit extends \Magefan\Blog\Controller\Adminhtml\Comment
{
    // /**
    //  * Authorization level of a basic admin session
    //  *
    //  * @see _isAllowed()
    //  */
    // const ADMIN_RESOURCE = 'Magefan_Blog::comment';

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    // protected $blockRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context,$dataPersistor);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $commentId) {
                    /** @var \Magefan\Blog\Model\Block $comment */
                    $comment = $this->_getModel(false)->load($commentId);
                    try {
                        $comment->setData(array_merge($comment->getData(), $postItems[$commentId]));
                        $comment->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithcommentId(
                            $comment,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add block title to error message
     *
     * @param $block
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithcommentId($block, $errorText)
    {
        return '[Block ID: ' . $block->getId() . '] ' . $errorText;
    }
}
