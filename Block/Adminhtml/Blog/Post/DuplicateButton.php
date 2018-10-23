<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Block\Adminhtml\Blog\Post;
//use phpDocumentor\Reflection\Types\Parent;

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
        if (!$this->authorization->isAllowed("Magefan_Blog::post_create")) {
            return [];
        }
        return parent::getButtonData();
    }


}
