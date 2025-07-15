<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Adminhtml;

/**
 * Admin blog category
 */
class Category extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magefan_Blog';
        $this->_headerText = __('Category');
        $this->_addButtonLabel = __('Add New Category');
        parent::_construct();
        if (!$this->_authorization->isAllowed("Magefan_Blog::category_save")) {
            $this->removeButton('add');
        }
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->_authorization->isAllowed("Magefan_Blog::import")) {
            $onClick = "setLocation('" . $this->getUrl('*/import') . "')";
            $this->getToolbar()->addChild(
                'options_button',
                \Magento\Backend\Block\Widget\Button::class,
                ['label' => __('Import Categories'), 'onclick' => $onClick]
            );
        }
        return parent::_prepareLayout();
    }
}
