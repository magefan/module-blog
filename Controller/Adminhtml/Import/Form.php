<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Import;

/**
 * Blog prepare import controller
 */
class Form extends \Magento\Backend\App\Action
{
    /**
     * Prepare wordpress import
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        try {

            if (!$type) {
                throw new \Exception(__('Blog import type is not specified.'), 1);
            }
            $_type = ucfirst($type);

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magefan_Blog::import');
            $title = __('Blog Import from %1 Blog', $_type);
            $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
            $this->_addBreadcrumb($title, $title);

            $config = new \Magento\Framework\DataObject(
                (array)$this->_getSession()->getData('import_' . $type . '_form_data', true) ?: []
            );

            $this->_objectManager->get(\Magento\Framework\Registry::class)->register('import_config', $config);

            $this->_view->renderLayout();

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong: ').' '.$e->getMessage());
            $this->_redirect('*/*/index');
        }
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
