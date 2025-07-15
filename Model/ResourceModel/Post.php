<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Model\ResourceModel;

/**
 * Blog post resource model
 */
class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->dateTime = $dateTime;
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
     * Retrieve date object
     * @return \Magento\Framework\Stdlib\DateTime
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Process post data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $condition = ['post_id = ?' => (int)$object->getId()];
        $tableSufixs = [
            'store',
            'category',
            'tag',
            'relatedproduct',
            'relatedpost',
        ];
        foreach ($tableSufixs as $sufix) {
            $this->getConnection()->delete(
                $this->getTable('magefan_blog_post_' . $sufix),
                ($sufix == 'relatedpost')
                    ? ['related_id = ?' => (int)$object->getId()]
                    : $condition
            );
        }

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
        foreach (['publish_time', 'custom_theme_from', 'custom_theme_to'] as $field) {
            $value = $object->getData($field) ?: null;
            $object->setData($field, $this->dateTime->formatDate($value));
        }

        $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magefan\Blog\Model\ResourceModel\PageIdentifierGenerator::class);
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key cannot be made of only numbers.')
            );
        }

        $id = $this->checkIdentifier($object->getData('identifier'), $object->getData('store_ids'));
        if ($id && $id !== $object->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('URL key is already in use by another blog item.')
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
        $newIds = (array)$object->getStoreIds();
        if (!$newIds || in_array(0, $newIds)) {
            $newIds = [0];
        }

        $this->_updateLinks($object, $newIds, $oldIds, 'magefan_blog_post_store', 'store_id');

        /* Save category & tag links */
        foreach (['category' => 'categories', 'tag' => 'tags'] as $linkType => $dataKey) {
            $newIds = (array)$object->getData($dataKey);
            foreach ($newIds as $key => $id) {
                if (!$id) { // e.g.: zero
                    unset($newIds[$key]);
                }
            }
            if (is_array($newIds)) {
                $lookup = 'lookup' . ucfirst($linkType) . 'Ids';
                $oldIds = $this->$lookup($object->getId());
                $this->_updateLinks(
                    $object,
                    $newIds,
                    $oldIds,
                    'magefan_blog_post_' . $linkType,
                    $linkType . '_id'
                );
            }
        }

        /* Save related post & product links */
        if ($links = $object->getData('links')) {
            if (is_array($links)) {
                foreach (['post', 'product'] as $linkType) {
                    if (isset($links[$linkType]) && is_array($links[$linkType])) {
                        $linksData = $links[$linkType];
                        $lookup = 'lookupRelated' . ucfirst($linkType) . 'Ids';
                        $oldIds = $this->$lookup($object->getId());
                        $this->_updateLinks(
                            $object,
                            array_keys($linksData),
                            $oldIds,
                            'magefan_blog_post_related' . $linkType,
                            'related_id',
                            $linksData
                        );
                    }
                }
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function incrementViewsCount(\Magento\Framework\Model\AbstractModel $object): void
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            ['views_count' => $object->getData('views_count') + 1],
            ['post_id = ?' => $object->getId()]
        );
    }

    /**
     * Update post connections
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  Array $newRelatedIds
     * @param  Array $oldRelatedIds
     * @param  String $tableName
     * @param  String  $field
     * @param  Array  $rowData
     * @return void
     */
    public function updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        array $newRelatedIds,
        array $oldRelatedIds,
        $tableName,
        string $field,
        $rowData = []
    ) {
        return $this->_updateLinks($object, $newRelatedIds, $oldRelatedIds, $tableName, $field, $rowData);
    }

    /**
     * Update post connections
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
        string $field,
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
            $where = ['post_id = ?' => (int)$object->getId(), $field.' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $id) {
                $id = (int)$id;
                $data[] = array_merge(
                    ['post_id' => (int)$object->getId(), $field => $id],
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

            $categories = $this->lookupCategoryIds($object->getId());
            $object->setCategories($categories);

            $tags = $this->lookupTagIds($object->getId());
            $object->setTags($tags);
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
    protected function _getLoadByIdentifierSelect($identifier, $storeIds)
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
            $storeIds
        );

        return $select;
    }

    /**
     *  Check whether post identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object): int|false
    {
        return preg_match('/^[0-9]+$/', (string)$object->getData('identifier'));
    }

    /**
     *  Check whether post identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object): int|false
    {
        return preg_match('/^([^?#<>@!&*()$%^\\+=,{}"\']+)?$/', (string)$object->getData('identifier'));
    }

    /**
     * Check if post identifier exist for specific store
     * return post id if post exists
     *
     * @param string $identifier
     * @param int|array $storeId
     * @return false|string
     */
    public function checkIdentifier($identifier, $storeIds): string|false
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $select = $this->_getLoadByIdentifierSelect($identifier, $storeIds);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns(['cp.post_id', 'cp.identifier'])->order('cps.store_id DESC')->limit(1);

        $row = $this->getConnection()->fetchRow($select);
        if (isset($row['post_id']) && isset($row['identifier'])
            && $row['identifier'] == $identifier) {
            return (string)$row['post_id'];
        }

        return false;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupStoreIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_store', 'store_id');
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupCategoryIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_category', 'category_id');
    }

    /**
     * Get tag ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupTagIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_tag', 'tag_id');
    }

    /**
     * Get related post ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupRelatedPostIds($postId)
    {
        return $this->_lookupIds($postId, 'magefan_blog_post_relatedpost', 'related_id');
    }

    /**
     * Get related product ids to which specified item is assigned
     *
     * @param int $postId
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
    public function lookupIds($postId, $tableName, $field)
    {
        return $this->_lookupIds($postId, $tableName, $field);
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

    /**
     * Get rows to which specified item is assigned
     * @param  int $postId
     * @param  string $tableName
     * @param  string $field
     * @return array
     */
    protected function _lookupAll($postId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'post_id = ?',
            (int)$postId
        );

        return $adapter->fetchAll($select);
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return 'post';
    }
}
