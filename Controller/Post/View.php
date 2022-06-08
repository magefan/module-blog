<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Post;

/**
 * Blog post view
 */
class View extends \Magefan\Blog\App\Action\Action
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $url;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magefan\Blog\Model\Url $url
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magefan\Blog\Model\Url $url
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->url = $url ?: $this->_objectManager->get(\Magefan\Blog\Model\Url::class);
    }

    /**
     * View Blog post action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }

        $post = $this->_initPost();

        if (!$post) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setHttpResponseCode(301);
            $resultRedirect->setPath($this->url->getBaseUrl());
            return $resultRedirect;
        }

        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_blog_post', $post);
        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, $post);
        return $resultPage;
    }

    /**
     * Init Post
     *
     * @return \Magefan\Blog\Model\Post || false
     */
    protected function _initPost()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return false;
        }

        $secret = (string)$this->getRequest()->getParam('secret');
        $storeId = $this->_storeManager->getStore()->getId();

        $post = $this->_objectManager->create(\Magefan\Blog\Model\Post::class)->load($id);

        if (!$post->isVisibleOnStore($storeId) && !$post->isValidSecret($secret)) {
            return false;
        }

        if ($post->isValidSecret($secret)) {
            $post->setIsPreviewMode(true);
        }

        $post->setStoreId($storeId);

        if ($category = $this->_initCategory()) {
            $post->setData('parent_category', $category);
        }

        return $post;
    }

    /**
     * Init category
     *
     * @return \Magefan\Blog\Model\category || false
     */
    protected function _initCategory()
    {
        $id = (int)$this->getRequest()->getParam('category_id');
        if (!$id) {
            return false;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $category = $this->_objectManager->create(\Magefan\Blog\Model\Category::class)->load($id);

        if (!$category->isVisibleOnStore($storeId)) {
            return false;
        }

        $category->setStoreId($storeId);

        return $category;
    }
}
