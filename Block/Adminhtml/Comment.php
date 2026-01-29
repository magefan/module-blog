<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml;

/**
 * Admin blog comment
 */
class Comment extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_comment';
        $this->_blockGroup = 'Magefan_Blog';
        //$this->_addButtonLabel = __('Add New Comment');
        parent::_construct();
        $this->removeButton('add');
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->_authorization->isAllowed("Magefan_BlogImport::import")) {
            $onClick = "setLocation('" . $this->getUrl('blogimport/import') . "')";

            $this->getToolbar()->addChild(
                'options_button',
                \Magento\Backend\Block\Widget\Button::class,
                ['label' => __('Import Comments'), 'onclick' => $onClick]
            );
        }
        return parent::_prepareLayout();
    }
}
