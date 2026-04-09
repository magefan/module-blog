<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model\ResourceModel\Author;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magefan\Blog\Api\AuthorCollectionInterface;

/**
 * Blog author collection
 */
class Collection extends AbstractCollection implements AuthorCollectionInterface
{

    /**
     * @var string
     */
    protected $_idFieldName = 'user_id';

    /**
     * Initialize model and resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magefan\Blog\Model\Author::class, \Magefan\Blog\Model\ResourceModel\Author::class);
    }

    /**
     * Authors are admin users and not store-scoped; method exists for interface compatibility.
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter(int $storeId): self
    {
        return $this;
    }
}
