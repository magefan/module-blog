<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Adminhtml\Import;

/**
 * Blog available imports list controller
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Start available import execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): void
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magefan_Blog::import');
        $title = __('Blog Import');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_Blog::import');
    }
}
