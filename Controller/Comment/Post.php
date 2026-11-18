<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Comment;

/**
 * Blog comment post
 */
class Post extends \Magefan\Blog\App\Action\Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Comment model factory
     *
     * @var \Magefan\Blog\Model\CommentFactory
     */
    protected $commentFactory;

    /**
     * Post model factory
     *
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $postFactory;

    /**
     * Core form key validator
     *
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment
     */
    protected $commentResource;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post
     */
    protected $postResource;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magefan\Blog\Model\CommentFactory $commentFactory
     * @param\Magefan\Blog\Model\PostFactory $postFactory,
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Magefan\Blog\Model\ResourceModel\Comment $commentResource
     * @param \Magefan\Blog\Model\ResourceModel\Post $postResource
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magefan\Blog\Model\CommentFactory $commentFactory,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magefan\Blog\Model\ResourceModel\Comment $commentResource,
        \Magefan\Blog\Model\ResourceModel\Post $postResource
    ) {
        $this->postResource = $postResource;
        $this->commentResource = $commentResource;
        $this->customerSession = $customerSession;
        $this->commentFactory = $commentFactory;
        $this->postFactory = $postFactory;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * View blog homepage action
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();

        if (!$this->formKeyValidator->validate($request)) {
            $this->getResponse()->setRedirect(
                $this->_redirect->getRefererUrl()
            );
            return;
        }

        if (!$this->moduleEnabled()) {
            $this->_forwardNoroute();
            return;
        }

        $comment = $this->commentFactory->create();
        $comment->setData($request->getPostValue());

        if ($this->customerSession->getCustomerGroupId()) {
            /* Customer */
            $comment->setCustomerId(
                $this->customerSession->getCustomerId()
            )->setAuthorNickname(
                $this->customerSession->getCustomer()->getName()
            )->setAuthorEmail(
                $this->customerSession->getCustomer()->getEmail()
            )->setAuthorType(
                \Magefan\Blog\Model\Config\Source\AuthorType::CUSTOMER
            );
        } elseif ($this->getConfigValue(
            \Magefan\Blog\Helper\Config::GUEST_COMMENT
        )) {
            /* Guest can post review */
            if (!trim($request->getParam('author_nickname')) || !trim($request->getParam('author_email'))) {
                $this->getResponse()->setBody(json_encode([
                    'success' => false,
                    'message' => __('Please enter your name and email'),
                ]));
                return;
            }
            
            $comment->setCustomerId(0)->setAuthorType(
                \Magefan\Blog\Model\Config\Source\AuthorType::GUEST
            );
        } else {
            /* Guest cannot post review */
            $this->getResponse()->setBody(json_encode([
                'success' => false,
                'message' => __('Login to submit comment'),
            ]));
            return;
        }

        /* Unset sensitive data */
        foreach (['comment_id', 'creation_time', 'update_time', 'status'] as $key) {
            $comment->unsetData($key);
        }

        /* Set default status */
        $comment->setStatus(
            $this->getConfigValue(
                \Magefan\Blog\Helper\Config::COMMENT_STATUS
            )
        );

        try {
            $post = $this->initPost();
            if (!$post) {
                throw new \Exception(__('You cannot post comment. Blog post is not longer exist.'), 1);
            }

            if ($request->getParam('parent_id')) {
                $parentComment = $this->initParentComment();
                if (!$parentComment) {
                    throw new \Exception(__('You cannot reply to this comment. Comment is not longer exist.'), 1);
                }

                if (!$parentComment->getPost()
                    || $parentComment->getPost()->getId() != $post->getId()
                    || $parentComment->isReply()
                ) {
                    throw new \Exception(__('You cannot reply to this comment.'), 1);
                }

                $comment->setParentId($parentComment->getId());
            }

            $this->commentResource->save($comment);
        } catch (\Exception $e) {
            $this->getResponse()->setBody(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
            return;
        }

        $this->getResponse()->setBody(json_encode([
            'success' => true,
            'message' => ($comment->getStatus() == \Magefan\Blog\Model\Config\Source\CommentStatus::PENDING)
                ? __('You submitted your comment for moderation.')
                : __('Thank you for your comment.')
        ]));
    }

    /**
     * Initialize and check post
     *
     * @return \Magefan\Blog\Model\Post|bool
     */
    protected function initPost()
    {
        $postId = (int)$this->getRequest()->getParam('post_id');
        $post = $this->postFactory->create();
        $this->postResource->load($post, $postId);
        if (!$post->getIsActive()) {
            return false;
        }
        return $post;
    }

    /**
     * Initialize and check parent comment
     *
     * @return \Magefan\Blog\Model\Comment|bool
     */
    protected function initParentComment()
    {
        $commentId = (int)$this->getRequest()->getParam('parent_id');

        $comment = $this->commentFactory->create();
        $this->commentResource->load($comment, $commentId);
        if (!$comment->isActive()) {
            return false;
        }

        return $comment;
    }
}
