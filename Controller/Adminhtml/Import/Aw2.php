<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Import;

/**
 * Blog aw2 import controller
 */
class Aw2 extends \Magento\Backend\App\Action
{
    /**
     * Prepare aw import
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magefan_Blog::import');
        $title = __('Blog Import from AheadWorks M2');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);

        $config = new \Magento\Framework\DataObject(
            (array)$this->_getSession()->getData('import_aw2_form_data', true) ?: []
        );

        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('import_config', $config);

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
