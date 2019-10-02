<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Grid\Column;

/**
 * Admin blog grid categories
 */
class Categories extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_rendererTypes['category'] = \Magefan\Blog\Block\Adminhtml\Grid\Column\Render\Category::class;
        $this->_filterTypes['category'] = \Magefan\Blog\Block\Adminhtml\Grid\Column\Filter\Category::class;
    }
}
