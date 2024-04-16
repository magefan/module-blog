<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel;

/**
 * Blog tag resource model
 */
class Tag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magefan_blog_tag', 'tag_id');
    }

    /**
     * Process tag data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['tag_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('magefan_blog_post_tag'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process tag data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setTitle(
            trim(($object->getTitle()))
        );

        $tag = $object->getCollection()
            ->addFieldToFilter('title', $object->getTitle())
            ->addFieldToFilter('tag_id', ['neq' => $object->getId()])
            ->setPageSize(1)
            ->getFirstItem();
        if ($tag->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The tag is already exist.')
            );
        }

        $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magefan\Blog\Model\ResourceModel\PageIdentifierGenerator::class);
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The tag URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The tag URL key cannot be made of only numbers.')
            );
        }

        $id = $this->checkIdentifier($object->getData('identifier'), $object->getData('store_ids'));
        if ($id && $id !== $object->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('URL key is already in use by another tag item.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Assign tag to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldIds = $this->lookupStoreIds($object->getId());
        $newIds = (array)$object->getStoreIds();
        if (!$newIds || in_array(0, $newIds)) {
            $newIds = [0];
        }

        $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_tag_store', 'store_id');

        return parent::_afterSave($object);
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
        if (!is_numeric($value) && null === $field) {
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
            $storeIds = $this->lookupStoreIds($object->getId());
            $object->setData('store_ids', $storeIds);
        }

        return parent::_afterLoad($object);
    }

    /**
     *  Check whether tag identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', (string)$object->getData('identifier'));
    }

    /**
     *  Check whether tag identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^([^?#<>@!&*()$%^\\+=,{}"\']+)?$/', (string)$object->getData('identifier'));
    }

    /**
     * Check if tag identifier exist for specific store
     * return tag id if tag exists
     *
     * @param string $identifier
     * @param int|array $storeId
     * @return false|string
     */
    public function checkIdentifier($identifier, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $select = $this->_getLoadByIdentifierSelect($identifier, $storeIds);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns(['cp.tag_id', 'cp.identifier'])->order('cps.store_id DESC')->limit(1);

     

        $row = $this->getConnection()->fetchRow($select);
        if (isset($row['tag_id']) && isset($row['identifier'])
            && $row['identifier'] == $identifier) {
            return (string)$row['tag_id'];
        }

        return false;
    }

    /**
     * Check if tag identifier exist for specific store
     * return tag id if tag exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByIdentifierSelect($identifier, $storeIds)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('magefan_blog_tag_store')],
            'cp.tag_id = cps.tag_id',
            []
        )->where(
            'cp.identifier = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $storeIds
        );

        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $tagId
     * @return array
     */
    public function lookupStoreIds($tagId)
    {
        return $this->_lookupIds($tagId, 'magefan_blog_tag_store', 'store_id');
    }

    /**
     * Get ids to which specified item is assigned
     * @param  int $tagId
     * @param  string $tableName
     * @param  string $field
     * @return array
     */
    protected function _lookupIds($tagId, $tableName, $field)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'tag_id = ?',
            (int)$tagId
        );

        return $adapter->fetchCol($select);
    }

    /**
     * Update tag connections
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  Array $newRelatedIds
     * @param  Array $oldRelatedIds
     * @param  String $tableName
     * @param  String  $field
     * @param  Array  $rowData
     * @return void
     */
    protected function _updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        array $newRelatedIds,
        array $oldRelatedIds,
        $tableName,
        $field,
        $rowData = []
    ) {
        $table = $this->getTable($tableName);

        if ($object->getId() && empty($rowData)) {
            $currentData = $this->_lookupAll($object->getId(), $tableName, '*');
            foreach ($currentData as $item) {
                $rowData[$item[$field]] = $item;
            }
        }

        $insert = $newRelatedIds;
        $delete = $oldRelatedIds;

        if ($delete) {
            $where = ['tag_id = ?' => (int)$object->getId(), $field.' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $id) {
                $id = (int)$id;
                $data[] = array_merge(
                    ['tag_id' => (int)$object->getId(), $field => $id],
                    (isset($rowData[$id]) && is_array($rowData[$id])) ? $rowData[$id] : []
                );
            }

            /* Fix if some rows have extra data */
            $allFields = [];
            foreach ($data as $i => $row) {
                foreach ($row as $key => $value) {
                    $allFields[$key] = $key;
                }
            }
            foreach ($data as $i => $row) {
                foreach ($allFields as $key) {
                    if (!array_key_exists($key, $row)) {
                        $data[$i][$key] = null;
                    }
                }
            }
            /* End fix */

            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Get rows to which specified item is assigned
     * @param  int $tagId
     * @param  string $tableName
     * @param  string $field
     * @return array
     */
    protected function _lookupAll($tagId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'tag_id = ?',
            (int)$tagId
        );

        return $adapter->fetchAll($select);
    }
}
