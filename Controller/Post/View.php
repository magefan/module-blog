<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
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
            return $this->_forwardNoroute();
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
        $id = $this->getRequest()->getParam('id');
        $secret = $this->getRequest()->getParam('secret');
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
        $id = $this->getRequest()->getParam('category_id');

        $storeId = $this->_storeManager->getStore()->getId();

        $category = $this->_objectManager->create(\Magefan\Blog\Model\Category::class)->load($id);

        if (!$category->isVisibleOnStore($storeId)) {
            return false;
        }

        $category->setStoreId($storeId);

        return $category;
    }
}
