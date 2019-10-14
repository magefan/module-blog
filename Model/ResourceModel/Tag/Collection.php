<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Tag;

/**
 * Blog tag collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magefan\Blog\Model\Tag::class, \Magefan\Blog\Model\ResourceModel\Tag::class);
    }

    /**
     * Add active filter to collection
     * @return self
     */
    public function addActiveFilter()
    {
        return $this
            ->addFieldToFilter('main_table.is_active', \Magefan\Blog\Model\Tag::STATUS_ENABLED);
    }
}
