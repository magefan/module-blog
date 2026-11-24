<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Post;

/**
 * Class Duplicate Button Block
 */
class DuplicateButton extends \Magefan\Community\Block\Adminhtml\Edit\DuplicateButton
{
    /**
     * Retrieves button data if the user is authorized to save
     *
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::post_save")) {
            return [];
        }
        return parent::getButtonData();
    }
}
