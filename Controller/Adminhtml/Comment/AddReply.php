<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Comment;

use Magefan\Blog\Model\Comment;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magefan\Blog\Model\CommentFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Message\Manager;

/**
 * Blog comment reply controller
 */
class AddReply extends \Magefan\Blog\Controller\Adminhtml\Comment
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::comment_reply';

    /**
     * @var CommentFactory
     */
    private $comment;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param CommentFactory $comment
     * @param Session $session
     * @param Context $context
     * @param Manager $manager
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        CommentFactory         $comment,
        Session                $session,
        Context                $context,
        Manager                $manager,
        DataPersistorInterface $dataPersistor)
    {
        $this->comment = $comment;
        $this->session = $session;
        $this->manager = $manager;
        parent::__construct($context, $dataPersistor);
    }

    /**
     * @return Redirect|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $parentCommentId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setHttpResponseCode(301);

        if ($parentCommentId) {
            try {
                $user = $this->session->getUser();
                $parentComment = $this->getCommentSingleton()->load($parentCommentId);
                $parentId = $parentComment->getParentId() ?: $parentComment->getCommentId();

                $reply = $this->getCommentSingleton();
                $reply->setData('parent_id', (int)$parentId);
                $reply->setData('admin_id', (int)$user->getUserId());
                $reply->setData('post_id', (int)$parentComment->getPostId());
                $reply->setData('status', '0');
                $reply->setData('author_type', '2');
                $reply->setData('author_nickname', $user->getUsername());
                $reply->setData('author_email', $user->getEmail());
                $reply->setData('text', _('Please type your comment reply here...'));
                $reply = $reply->save();
            } catch (\Exception $exception) {
                $this->messageManager->addError(_('Something wrong: ' . $exception->getMessage()));
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }

            $replyCommentId = $reply->getCommentId();
            $resultRedirect->setPath('*/*/edit', ['id' => $replyCommentId]);
        } else {
            $resultRedirect->setPath('*/*/index');
        }

        return $resultRedirect;
    }

    /**
     * @return Comment
     */
    protected function getCommentSingleton()
    {
        return $this->comment->create();
    }
}
