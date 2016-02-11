<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Category;

/**
 * Blog category view
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * View blog category action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $category = $this->_objectManager->create('Magefan\Blog\Model\Category')->load($id);
        if (!$category->getId()) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_blog_category', $category);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
