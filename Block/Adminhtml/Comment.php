<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml;

/**
 * Admin blog post
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
        $this->_controller = 'adminhtml_Blog';
        $this->_blockGroup = 'Magefan_Blog';
        //$this->_addButtonLabel = __('Add New Comment');
        parent::_construct();
        $this->removeButton('add');
    }
}
