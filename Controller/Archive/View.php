<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Archive;

/**
 * Blog archive view
 */
class View extends \Magefan\Blog\App\Action\Action
{
    /**
     * View blog archive action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }

        $date = $this->getRequest()->getParam('date');

        $date = explode('-', $date);
        $date[2] = '01';
        $time = strtotime(implode('-', $date));

        if (!$time || count($date) != 3) {
            return $this->_forwardNoroute();
        }

        $registry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        $registry->register('current_blog_archive_year', (int)$date[0]);
        $registry->register('current_blog_archive_month', (int)$date[1]);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
