<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_blog_author', $author);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Init author
     *
     * @return \Magefan\Blog\Model\Author || false
     */
    protected function _initAuthor()
    {
        $id = $this->getRequest()->getParam('id');

        $author = $this->_objectManager->create('Magefan\Blog\Model\Author')->load($id);

        if (!$author->getId()) {
            return false;
        }

        return $author;
    }

}
