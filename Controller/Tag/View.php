<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Tag;

use \Magento\Store\Model\ScopeInterface;

/**
 * Blog tag posts view
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * View blog author action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $config = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $tag = $this->_initTag();
        if (!$tag) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_blog_tag', $tag);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Init author
     *
     * @return \Magefan\Blog\Model\Tag || false
     */
    protected function _initTag()
    {
        $id = $this->getRequest()->getParam('id');

        $tag = $this->_objectManager->create('Magefan\Blog\Model\Tag')->load($id);

        if (!$tag->getId()) {
            return false;
        }

        return $tag;
    }

}
