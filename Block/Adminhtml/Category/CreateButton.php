<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magefan\Blog\Block\Adminhtml\Category;
/**
 * Class CreateButton
 */
class CreateButton extends  \Magefan\Community\Block\Adminhtml\Edit\CreateButton
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
