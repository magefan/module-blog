<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Author;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magefan\Blog\Api\AuthorCollectionInterface;

/**
 * Blog author collection
 */
class Collection extends AbstractCollection implements AuthorCollectionInterface
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'user_id';
    
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magefan\Blog\Model\Author::class, \Magefan\Blog\Model\ResourceModel\Author::class);
    }
}
