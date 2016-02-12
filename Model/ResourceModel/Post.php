<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel;

/**
 * Blog category resource model
 */
class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        $this->_date = $date;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magefan_blog_post', 'post_id');
    }

    /**
     * Process post data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['post_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('magefan_blog_post_store'), $condition);
        $this->getConnection()->delete($this->getTable('magefan_blog_post_category'), $condition);
        $this->getConnection()->delete($this->getTable('magefan_blog_post_relatedproduct'), $condition);

        $this->getConnection()->delete($this->getTable('magefan_blog_post_relatedpost'), $condition);
        $this->getConnection()->delete($this->getTable('magefan_blog_post_relatedpost'),
            ['related_id = ?' => (int)$object->getId()]
        );

        return parent::_beforeDelete($object);
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key cannot be made of only numbers.')
            );
        }

        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreationTime()) {
            $object->setCreationTime($gmtDate);
        }

        if (!$object->getPublishTime()) {
            $object->setPublishTime($object->getCreationTime());
        }

        $object->setUpdateTime($gmtDate);

        return parent::_beforeSave($object);
    }

    /**
     * Assign post to store views, categories, related posts, etc.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldIds = $this->lookupStoreIds($object->getId());
        $newIds = (array)$object->getStores();
        if (empty($newIds)) {
            $newIds = (array)$object->getStoreId();
        }
        $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_post_store', 'store_id');



        $newIds = (array)$object->getCategories();
        if (is_array($newIds)) {
            $oldIds = $this->lookupCategoryIds($object->getId());
            $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_post_category', 'category_id');
        }


        $newIds = $object->getRelatedPostIds();
        if (is_array($newIds)) {
            $oldIds = $this->lookupRelatedPostIds($object->getId());
            $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_post_relatedpost', 'related_id');
        }


        $newIds = $object->getRelatedProductIds();
        if (is_array($newIds)) {
            $oldIds = $this->lookupRelatedProductIds($object->getId());
            $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_post_relatedproduct', 'related_id');
        }


        return parent::_afterSave($object);
    }

    /**
     * Update post connections
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  Array $newRelatedIds
     * @param  Array $oldRelatedIds
     * @param  String $tableName
     * @param  String  $field
     * @return void
     */
    protected function _updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        Array $newRelatedIds,
        Array $oldRelatedIds,
        $tableName,
        $field
    ) {
        $table = $this->getTable($tableName);

        $insert = array_diff($newRelatedIds, $oldRelatedIds);
        $delete = array_diff($oldRelatedIds, $newRelatedIds);

        if ($delete) {
            $where = ['post_id = ?' => (int)$object->getId(), $field.' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['post_id' => (int)$object->getId(), $field => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);

            $categories = $this->lookupCategoryIds($object->getId());
            $object->setCategories($categories);

            $relatedPosts = $this->lookupRelatedPostIds($object->getId());
            $object->setRelatedPostIds($relatedPosts);

            $relatedProducts = $this->lookupRelatedProductIds($object->getId());
            $object->setRelatedProductIds($relatedProducts);

        }

        return parent::_afterLoad($object);
    }

    /**
     * Check if post identifier exist for specific store
     * return post id if post exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('magefan_blog_post_store')],
            'cp.post_id = cps.post_id',
            []
        )->where(
            'cp.identifier = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $store
        );

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive)
                ->where('cp.publish_time <= ?', $this->_date->gmtDate());
        }
        return $select;
    }

    /**
     *  Check whether post identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether post identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     * Check if post identifier exist for specific store
     * return post id if post exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.post_id')->order('cps.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupStoreIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_store', 'store_id');
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupCategoryIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_category', 'category_id');
    }

    /**
     * Get related post ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupRelatedPostIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_relatedpost', 'related_id');
    }

    /**
     * Get related product ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupRelatedProductIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_relatedproduct', 'related_id');
    }

    /**
     * Get ids to which specified item is assigned
     * @param  int $postId
     * @param  string $tableName
     * @param  string $field
     * @return array
     */
    protected function _lookupIds($postId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'post_id = ?',
            (int)$postId
        );

        return $adapter->fetchCol($select);
    }

}
