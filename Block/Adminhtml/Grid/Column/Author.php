<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
        $this->_rendererTypes['author'] = 'Magefan\Blog\Block\Adminhtml\Grid\Column\Render\Author';
        $this->_filterTypes['author'] = 'Magefan\Blog\Block\Adminhtml\Grid\Column\Filter\Author';
    }
}
