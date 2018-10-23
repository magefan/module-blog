<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magefan\Blog\Block\Adminhtml\Blog\Comment;

/**
 * Class SaveAndContinueButton
 */
class SaveAndContinueButton  extends \Magefan\Community\Block\Adminhtml\Edit\SaveAndContinueButton
{
    /**
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::comment_update")) {
            return [];
        }
        return parent::getButtonData();
    }
}
