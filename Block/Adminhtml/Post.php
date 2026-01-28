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
 * Admin blog post
 */
class Post extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'Magefan_Blog';
        $this->_headerText = __('Post');
        $this->_addButtonLabel = __('Add New Post');

        parent::_construct();
        if (!$this->_authorization->isAllowed("Magefan_Blog::post_save")) {
            $this->removeButton('add');
        }
    }

    /**
     * Prepares the layout and adds an import button to the toolbar if the user has the necessary permissions.
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
                ['label' => __('Import Posts'), 'onclick' => $onClick]
            );
        }
        return parent::_prepareLayout();
    }
}
