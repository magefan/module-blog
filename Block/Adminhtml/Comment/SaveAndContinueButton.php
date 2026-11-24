<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Comment;

/**
 * Class Save And ContinueButton Block
 */
class SaveAndContinueButton extends \Magefan\Community\Block\Adminhtml\Edit\SaveAndContinueButton
{
    /**
     * Retrieves button data for the authorized context.
     *
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::comment_save")) {
            return [];
        }
        return parent::getButtonData();
    }
}
