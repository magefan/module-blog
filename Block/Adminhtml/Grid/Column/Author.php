<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Grid\Column;

/**
 * Admin blog grid author
 */
class Author extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_rendererTypes['author'] = \Magefan\Blog\Block\Adminhtml\Grid\Column\Render\Author::class;
        $this->_filterTypes['author'] = \Magefan\Blog\Block\Adminhtml\Grid\Column\Filter\Author::class;
    }
}
