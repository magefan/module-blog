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
    protected $_allowedKey = 'Magefan_Blog::comment_save';

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
     * @var \Magefan\Blog\Model\ResourceModel\Comment
     */
    protected $commentResource;

    /**
     * @param CommentFactory $comment
     * @param Session $session
     * @param Context $context
     * @param Manager $manager
     * @param DataPersistorInterface $dataPersistor
     * @param \Magefan\Blog\Model\ResourceModel\Comment $commentResource
     */
    public function __construct(
        CommentFactory         $comment,
        Session                $session,
        Context                $context,
        Manager                $manager,
        DataPersistorInterface $dataPersistor,
        \Magefan\Blog\Model\ResourceModel\Comment $commentResource
    ) {
        $this->commentResource = $commentResource;
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
        $parentCommentId = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($parentCommentId) {
            try {
                $user = $this->session->getUser();
                $parentComment = $this->comment->create();
                $this->commentResource->load($parentComment, $parentCommentId);
                $parentId = $parentComment->getParentId() ?: $parentComment->getCommentId();

                if (!$parentId) {
                    $resultRedirect->setPath('*/*/index');
                } else {

                    $reply = $this->comment->create();
                    $reply->setData('parent_id', (int)$parentId);
                    $reply->setData('admin_id', (int)$user->getUserId());
                    $reply->setData('post_id', (int)$parentComment->getPostId());
                    $reply->setData('status', '0');
                    $reply->setData('author_type', '2');
                    $reply->setData('author_nickname', $user->getUsername());
                    $reply->setData('author_email', $user->getEmail());
                    $reply->setData('text', _('Please type your comment reply here...'));
                    $this->commentResource->save($reply);

                    $replyCommentId = $reply->getCommentId();
                    $resultRedirect->setPath('*/*/edit', ['id' => $replyCommentId]);
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(_('Something wrong: ' . $exception->getMessage()));
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }
        } else {
            $resultRedirect->setPath('*/*/index');
        }

        return $resultRedirect;
    }
}
