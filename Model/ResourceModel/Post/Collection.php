<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Post;

/**
 * Blog post collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var \Magefan\Blog\Model\Category
     */
    protected $category;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null|\Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_date = $date;
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
        $this->_init(\Magefan\Blog\Model\Post::class, \Magefan\Blog\Model\ResourceModel\Post::class);
        $this->_map['fields']['post_id'] = 'main_table.post_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['category'] = 'category_table.category_id';
        $this->_map['fields']['tag'] = 'tag_table.tag_id';
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
                $condition = $condition[0] ?? $condition;
            }
        }

        if ($field === 'store_id' || $field === 'store_ids') {
            return $this->addStoreFilter($condition);
        }

        if ($field === 'category' || $field === 'categories' || $field === 'category_id') {
            return $this->addCategoryFilter($condition);
        }

        if ($field === 'tag' || $field === 'tag_id') {
            return $this->addTagFilter($condition);
        }

        if ($field === 'author' || $field === 'author_id') {
            return $this->addAuthorFilter($condition);
        }

        if ($field === 'search') {
            if (is_array($condition)) {
                $condition = array_shift($condition);
            }
            return $this->addSearchFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
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
     * Add "include in recent" filter to collection
     * @return $this
     */
    public function addRecentFilter()
    {
        return $this->addFieldToFilter('include_in_recent', 1);
    }

    /**
     * Add posts filter to collection
     * @param array|int|string  $category
     * @return $this
     */
    public function addPostsFilter($postIds)
    {
        if (!is_array($postIds)) {
            $postIds = explode(',', $postIds);
            foreach ($postIds as $key => $id) {
                $id = trim($id);
                if (!$id) {
                    unset($postIds[$key]);
                }
            }
        }

        if (!count($postIds)) {
            $postIds = [0];
        }

        $this->addFieldToFilter(
            'post_id',
            ['in' => $postIds]
        );
    }

    /**
     * Add category filter to collection
     * @param array|int|\Magefan\Blog\Model\Category  $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if (!$this->getFlag('category_filter_added')) {
            if ($category instanceof \Magefan\Blog\Model\Category) {
                $this->category = $category;
                $categories = $category->getChildrenIds();
                $categories[] = $category->getId();
            } else {
                $categories = $category;
                if (!is_array($categories)) {
                    $categories = [$categories];
                }
            }

            $connection = $this->getConnection();
            $tableName = $this->getTable('magefan_blog_category');

            if (is_numeric(key($categories))) {
                foreach ($categories as $k => $id) {
                    if (!is_numeric($id)) {
                        $select = $connection->select()
                            ->from(['t' => $tableName], 'category_id')
                            ->where('t.identifier = ?', $id);

                        $id = $connection->fetchOne($select);
                        if (!$id) {
                            $id = 0;
                        }

                        $categories[$k] = $id;
                    }
                }
                
            } else {
                $select = $connection->select()
                    ->from(['t' => $tableName], 'category_id')
                    ->where(
                        $connection->prepareSqlCondition('t.identifier', $categories)
                        . ' OR ' .
                        $connection->prepareSqlCondition('t.category_id', $categories)
                    );
                
                $categories = [];
                foreach ($connection->fetchAll($select) as $item) {
                    $categories[] = $item['category_id'];
                }
            }

            $this->addFilter('category', ['in' => $categories], 'public');
            $this->setFlag('category_filter_added', 1);
        }
        return $this;
    }

    /**
     * Add archive filter to collection
     * @param int $year
     * @param int $month
     * @return $this
     */
    public function addArchiveFilter($year, $month)
    {
        $this->getSelect()
            ->where('YEAR(publish_time) = ?', $year)
            ->where('MONTH(publish_time) = ?', $month);
        return $this;
    }

    /**
     * Add search filter to collection
     * @param string $term
     * @return $this
     */
    public function addSearchFilter($term)
    {
        $tagPostIds = [];
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['p' => $this->getTable('magefan_blog_post')],
                ['post_id']
            )->joinInner(
                ['pt' => $this->getTable('magefan_blog_post_tag')],
                'p.post_id = pt.post_id',
                ['']
            )->joinInner(
                ['t' => $this->getTable('magefan_blog_tag')],
                't.tag_id = pt.tag_id',
                ['tag_title' => 'title']
            )->where('t.title LIKE ?', '%' . $term . '%');

        foreach ($connection->fetchAll($select) as $item) {
            $tagPostIds[] = (int)$item['post_id'];
        }

        $tagPostIds = array_unique($tagPostIds);

        $advancedSortingEnabled = true;
        if (false !== stripos($term, ' as ')) {
            $advancedSortingEnabled = false;
        }

        if (count($tagPostIds)) {
            $this->addFieldToFilter(
                ['title', 'short_content', 'content', 'post_id'],
                [
                    ['like' => '%' . $term . '%'],
                    ['like' => '%' . $term . '%'],
                    ['like' => '%' . $term . '%'],
                    ['in' => $tagPostIds]
                ]
            );

            if ($advancedSortingEnabled) {
                $this->addExpressionFieldToSelect(
                    'search_rate',
                    '(0
                      + FORMAT(MATCH (title, meta_keywords, meta_description, identifier, content) AGAINST ("{{term}}"), 4) 
                      + IF(main_table.post_id IN (' . implode(',', $tagPostIds) . '), "1", "0"))',
                    [
                        'term' => $this->getConnection()->quote($term)
                    ]
                );
            } else {
                $this->addExpressionFieldToSelect('search_rate', ' publish_time', []);
            }
        } else {
            $this->addFieldToFilter(
                ['title', 'short_content', 'content'],
                [
                    ['like' => '%' . $term . '%'],
                    ['like' => '%' . $term . '%'],
                    ['like' => '%' . $term . '%']
                ]
            );

            if ($advancedSortingEnabled) {
                $this->addExpressionFieldToSelect(
                    'search_rate',
                    '(0
                      + FORMAT(MATCH (title, meta_keywords, meta_description, identifier, content) AGAINST ("{{term}}"), 4))',
                    [
                        'term' => $this->getConnection()->quote($term)
                    ]
                );
            } else {
                $this->addExpressionFieldToSelect('search_rate', ' publish_time', []);
            }
        }

        return $this;
    }

    /**
     * Add tag filter to collection
     * @param array|int|string|\Magefan\Blog\Model\Tag  $tag
     * @return $this
     */
    public function addTagFilter($tag)
    {
        if (!$this->getFlag('tag_filter_added')) {
            if ($tag instanceof \Magefan\Blog\Model\Tag) {
                $tag = [$tag->getId()];
            }

            if (!is_array($tag)) {
                $tag = [$tag];
            }

            $connection = $this->getConnection();
            $tableName = $this->getTable('magefan_blog_tag');
            
            if (is_numeric(key($tag))) {
                foreach ($tag as $k => $id) {
                    if (!is_numeric($id)) {
                        $select = $connection->select()
                            ->from(['t' => $tableName], 'tag_id')
                            ->where('t.identifier = ?', $id);

                        $id = $connection->fetchOne($select);
                        if (!$id) {
                            $id = 0;
                        }

                        $tag[$k] = $id;
                    }
                }
            } else {
                $select = $connection->select()
                    ->from(['t' => $tableName], 'tag_id')
                    ->where(
                        $connection->prepareSqlCondition('t.identifier', $tag)
                        . ' OR ' .
                        $connection->prepareSqlCondition('t.tag_id', $tag)
                    );
                
                $tag = [];
                foreach ($connection->fetchAll($select) as $item) {
                    $tag[] = $item['tag_id'];
                }
            }

            $this->addFilter('tag', ['in' => $tag], 'public');
            $this->setFlag('tag_filter_added', 1);
        }
        return $this;
    }


    /**
     * Add author filter to collection
     * @param array|int|\Magefan\Blog\Model\Author  $author
     * @return $this
     */
    public function addAuthorFilter($author)
    {
        if (!$this->getFlag('author_filter_added')) {
            if ($author instanceof \Magefan\Blog\Model\Author) {
                $author = [$author->getId()];
            }

            if (!is_array($author)) {
                $author = [$author];
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $authorModel = $objectManager->get(\Magefan\Blog\Api\AuthorInterface::class);

            $firstKey = key($author);
            if ('in' == $firstKey) {
                $author = $author[$firstKey];
                if (!is_array($author)) {
                    $author = [$author];
                }
            }

            foreach ($author as $k => $id) {
                if (!is_numeric($id)) {
                    $id = $authorModel->checkIdentifier($id);

                    if (!$id) {
                        $id = 0;
                    }
                    $author[$k] = $id;
                }
            }

            $this->addFilter('author_id', ['in' => $author], 'public');
            $this->setFlag('author_filter_added', 1);
        }
        return $this;
    }

    /**
     * Add is_active filter to collection
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this
            ->addFieldToFilter('main_table.is_active', 1)
            ->addFieldToFilter('main_table.publish_time', ['lteq' => $this->_date->gmtDate()]);
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
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('post_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $tableName = $this->getTable('magefan_blog_post_store');
            $select = $connection->select()
                ->from(['cps' => $tableName])
                ->where('cps.post_id IN (?)', $items);

            $result = [];
            foreach ($connection->fetchAll($select) as $item) {
                if (!isset($result[$item['post_id']])) {
                    $result[$item['post_id']] = [];
                }
                $result[$item['post_id']][] = $item['store_id'];
            }

            if ($result) {
                foreach ($this as $item) {
                    $postId = $item->getData('post_id');
                    if (!isset($result[$postId])) {
                        continue;
                    }
                    if ($result[$postId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                    } else {
                        $storeId = $result[$item->getData('post_id')];
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_ids', $result[$postId]);
                }
            }

            foreach ($this as $item) {
                if ($this->_storeId) {
                    $item->setStoreId($this->_storeId);
                }

                if ($this->category) {
                    $item->setData('parent_category', $this->category);
                }
            }

            $map = [
                'category' => 'categories',
                'tag' => 'tags',
            ];

            foreach ($map as $key => $property) {
                $tableName = $this->getTable('magefan_blog_post_' . $key);
                $select = $connection->select()
                    ->from(['cps' => $tableName])
                    ->where('cps.post_id IN (?)', $items);

                $result = $connection->fetchAll($select);
                if ($result) {
                    $data = [];
                    foreach ($result as $item) {
                        $data[$item['post_id']][] = $item[$key . '_id'];
                    }

                    foreach ($this as $item) {
                        $postId = $item->getData('post_id');
                        if (isset($data[$postId])) {
                            $item->setData($property, $data[$postId]);
                        }
                    }
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
        foreach (['store', 'category', 'tag', 'author'] as $key) {

            if ($this->getFilter($key)) {

                $joinOptions = new \Magento\Framework\DataObject;
                $joinOptions->setData([
                    'key' => $key,
                    'fields' => [],
                    'fields' => [],
                ]);
                $this->_eventManager->dispatch(
                    'mfblog_post_collection_render_filter_join',
                    ['join_options' => $joinOptions]
                );
                $this->getSelect()->join(
                    [$key.'_table' => $this->getTable('magefan_blog_post_'.$key)],
                    'main_table.post_id = '.$key.'_table.post_id',
                    $joinOptions->getData('fields')
                )->group(
                    'main_table.post_id'
                );
            }
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        parent::setOrder($field, $direction);

        if (is_string($field) && $field == 'publish_time') {
            parent::setOrder('post_id', $direction);
        }
        return $this;
    }
}
