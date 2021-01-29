<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Tag;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog tag posts view
 */
class View extends \Magefan\Blog\App\Action\Action
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * View blog author action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }

        $tag = $this->_initTag();
        if (!$tag) {
            return $this->_forwardNoroute();
        }

        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('current_blog_tag', $tag);

        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, $tag);

        return $resultPage;
    }

    /**
     * Init author
     *
     * @return \Magefan\Blog\Model\Tag || false
     */
    protected function _initTag()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return false;
        }

        $storeId = $this->getStoreManager()->getStore()->getId();
        $tag = $this->_objectManager->create(\Magefan\Blog\Model\Tag::class)->load($id);

        if (!$tag->isVisibleOnStore($storeId)) {
            return false;
        }

        $tag->setStoreId($storeId);

        return $tag;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface|mixed
     */
    private function getStoreManager()
    {
        if (null === $this->_storeManager) {
            $this->_storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->_storeManager;
    }
}
