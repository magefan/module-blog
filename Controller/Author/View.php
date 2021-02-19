<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Author;

/**
 * Blog author posts view
 */
class View extends \Magefan\Blog\App\Action\Action
{
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

        $enabled = (int) $this->getConfigValue('mfblog/author/enabled');
        $pageEnabled = (int) $this->getConfigValue('mfblog/author/page_enabled');

        if (!$enabled || !$pageEnabled) {
            return $this->_forwardNoroute();
        }

        $author = $this->_initAuthor();
        if (!$author) {
            return $this->_forwardNoroute();
        }

        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('current_blog_author', $author);

        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, $author);
        return $resultPage;
    }

    /**
     * Init author
     *
     * @return \Magefan\Blog\Api\AuthorInterface || false
     */
    protected function _initAuthor()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return false;
        }

        $author = $this->_objectManager->create(\Magefan\Blog\Api\AuthorInterface::class)->load($id);

        if (!$author->isActive()) {
            return false;
        }

        return $author;
    }
}
