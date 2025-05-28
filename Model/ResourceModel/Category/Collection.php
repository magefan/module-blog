<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Category;

/**
 * Blog category collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_eventPrefix = 'mfblog_category_collection';

    /**
     * @inheritDoc
     */
    protected $_eventObject = 'blog_category_collection';

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'category_id';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var bool
     */
    protected $_previewFlag;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null|\Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magefan\Blog\Model\Category::class, \Magefan\Blog\Model\ResourceModel\Category::class);
        $this->_map['fields']['category_id'] = 'main_table.category_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            if (count($field) > 1) {
                return parent::addFieldToFilter($field, $condition);
            } elseif (count($field) === 1) {
                $field = $field[0];
                $condition = isset($condition[0]) ? $condition[0] : $condition;
            }
        }

        if ($field === 'store_id' || $field === 'store_ids') {
            return $this->addStoreFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add search filter to collection
     * @param string $term
     * @return $this
     */
    public function addSearchFilter(string $term)
    {
        return $this->addFieldToFilter('title', ['like' => '%' . $term . '%']);
    }

    /**
     * Add store filter to collection
     * @param array|int|\Magento\Store\Model\Store  $store
     * @param boolean $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store === null) {
            return $this;
        }

        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof \Magento\Store\Model\Store) {
                $this->_storeId = $store->getId();
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $this->_storeId = $store;
                $store = [$store];
            }

            if (in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $store)) {
                return $this;
            }

            if ($withAdmin) {
                $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store', ['in' => $store], 'public');
            $this->setFlag('store_filter_added', 1);
        }
        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }

    /**
     * Add enable filter to collection
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this->addFieldToFilter('is_active', 1);
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('category_id');
        if (count($items)) {

            foreach ($this as $item) {
                $categoryId = $item->getData('category_id');
                $storeIds = $this->getResource()->lookupStoreIds($categoryId);
                if (!$storeIds) {
                    continue;
                }
                if (in_array(0, $storeIds)) {
                    $stores = $this->_storeManager->getStores(false, true);
                    $storeId = current($stores)->getId();
                } else {
                    $storeId = $storeIds[0];
                }
                $item->setData('_first_store_id', $storeId);
                $item->setData('store_ids', $storeIds);
            }

            if ($this->_storeId) {
                foreach ($this as $item) {
                    $item->setStoreId($this->_storeId);
                }
            }
        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('magefan_blog_category_store')],
                'main_table.category_id = store_table.category_id',
                []
            )->group(
                'main_table.category_id'
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Retrieve gruped category childs
     * @return array
     */
    public function getGroupedChilds()
    {
        $childs = [];
        if (count($this)) {
            foreach ($this as $item) {
                $childs[$item->getParentId()][] = $item;
            }
        }
        return $childs;
    }

    /**
     * Retrieve tree ordered categories
     * @return array
     */
    public function getTreeOrderedArray()
    {
        $tree = [];
        if ($childs = $this->getGroupedChilds()) {
            $this->_toTree(0, $childs, $tree);
        }
        return $tree;
    }

    /**
     * Auxiliary function to build tree ordered array
     * @return array
     */
    protected function _toTree($itemId, $childs, &$tree)
    {
        if ($itemId) {
            $tree[] = $this->getItemById($itemId);
        }

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $i) {
                $this->_toTree($i->getId(), $childs, $tree);
            }
        }
    }

    /**
     * @return int
     */
    public function getStoreId():int
    {
        return (int)$this->_storeId;
    }
}
