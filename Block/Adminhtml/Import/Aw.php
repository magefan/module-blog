<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Import;

use Magento\Store\Model\ScopeInterface;

/**
 * AheadWorks import block
 */
class Aw extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Initialize aheadworks import block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magefan_Blog';
        $this->_controller = 'adminhtml_import';
        $this->_mode = 'aheadworks';

        parent::_construct();

        if (!$this->_isAllowedAction('Magefan_Blog::import')) {
            $this->buttonList->remove('save');
        } else {
            $this->updateButton(
                'save',
                'label',
                __('Start Import')
            );
        }

        $this->buttonList->remove('delete');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form save URL
     *
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/run', ['_current' => true]);
    }
}
