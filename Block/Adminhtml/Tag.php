<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml;

/**
 * Admin blog tag
 */
class Tag extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_tag';
        $this->_blockGroup = 'Magefan_Blog';
        $this->_headerText = __('Tag');
        $this->_addButtonLabel = __('Add New Tag');

        parent::_construct();
        if (!$this->_authorization->isAllowed("Magefan_Blog::tag_save")) {
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
                ['label' => __('Import Tags'), 'onclick' => $onClick]
            );
        }
        return parent::_prepareLayout();
    }
}
