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
 * Class Delete Button Block
 */
class DeleteButton extends \Magefan\Community\Block\Adminhtml\Edit\DeleteButton
{
    /**
     * Retrieves button data if the user is authorized to delete
     *
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::post_delete")) {
            return [];
        }
        return parent::getButtonData();
    }
}
