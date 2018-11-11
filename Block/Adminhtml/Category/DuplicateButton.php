<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Category;

/**
 * Class DuplicateButton
 */
class DuplicateButton extends \Magefan\Community\Block\Adminhtml\Edit\DuplicateButton
{
    /**
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::category_save")) {
            return [];
        }
        return parent::getButtonData();
    }
}
