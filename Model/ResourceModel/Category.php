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
 * Blog category resource model
 */
class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected static $allStoreIds;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
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
        $this->_init('magefan_blog_category', 'category_id');
    }

    /**
     * Process category data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['category_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('magefan_blog_category_store'), $condition);
        $this->getConnection()->delete($this->getTable('magefan_blog_post_category'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process category data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach (['custom_theme_from', 'custom_theme_to'] as $field) {
            $value = $object->getData($field) ?: null;
            $object->setData($field, $this->dateTime->formatDate($value));
        }

        $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magefan\Blog\Model\ResourceModel\PageIdentifierGenerator::class);
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The category URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The category URL key cannot be made of only numbers.')
            );
        }

        $id = $this->checkIdentifier($object->getData('identifier'), $object->getData('store_ids'));
        if ($id && $id !== $object->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('URL key is already in use by another blog item.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Assign category to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStoreIds = $this->lookupStoreIds($object->getId(), false);
        $newStoreIds = (array)$object->getStoreIds();
        if (!$newStoreIds || in_array(0, $newStoreIds)) {
            $newStoreIds = [0];
        }

        $table = $this->getTable('magefan_blog_category_store');
        $insert = array_diff($newStoreIds, $oldStoreIds);
        $delete = array_diff($oldStoreIds, $newStoreIds);

        if ($delete) {
            $where = ['category_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['category_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

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
     * Check if category identifier exist for specific store
     * return category id if category exists
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
            ['cps' => $this->getTable('magefan_blog_category_store')],
            'cp.category_id = cps.category_id',
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
     *  Check whether category identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object): int|false
    {
        return preg_match('/^[0-9]+$/', (string)$object->getData('identifier'));
    }

    /**
     *  Check whether category identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object): int|false
    {
        return preg_match('/^([^?#<>@!&*()$%^\\+=,{}"\']+)?$/', (string)$object->getData('identifier'));
    }

    /**
     * Check if category identifier exist for specific store
     * return page id if page exists
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
        $select->reset(\Zend_Db_Select::COLUMNS)->columns(['cp.category_id', 'cp.identifier'])->order('cps.store_id DESC')->limit(1);

        $row = $this->getConnection()->fetchRow($select);
        if (isset($row['category_id']) && isset($row['identifier'])
            && $row['identifier'] == $identifier) {
            return (string)$row['category_id'];
        }

        return false;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param $categoryId
     * @param bool $useCache
     * @return array|mixed
     */
    public function lookupStoreIds($categoryId, $useCache = true)
    {
        if (null === self::$allStoreIds || !$useCache) {
            $adapter = $this->getConnection();

            $select = $adapter->select()->from(
                $this->getTable('magefan_blog_category_store')
            );

            $result = [];
            foreach ($adapter->fetchAll($select) as $item) {
                if (!isset($result[$item['category_id']])) {
                    $result[$item['category_id']] = [];
                }
                $result[$item['category_id']][] = $item['store_id'];
            }

            self::$allStoreIds = $result;
        }

        if (isset(self::$allStoreIds[$categoryId])) {
            return self::$allStoreIds[$categoryId];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return 'category';
    }
}
