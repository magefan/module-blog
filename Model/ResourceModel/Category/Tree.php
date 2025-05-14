<?php

namespace Magefan\Blog\Model\ResourceModel\Category;

use Magefan\Blog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Data\Tree\Dbp;
use Magefan\Blog\Api\Data\CategoryManagementInterface;
use Magento\Framework\Data\Tree\Node;

class Tree extends Dbp
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * Categories resource collection
     *
     * @var Collection
     */
    protected $_collection;

    /**
     * @var integer
     */
    protected $_storeId = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_coreResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category
     */
    protected $_blogCategory;

    /**
     * @var null
     */
    protected $categoryPostsCount = null;

    /**
     * @var null
     */
    protected $categoryChildrenCount = null;

    /**
     * Tree constructor.
     * @param \Magefan\Blog\Model\ResourceModel\Category $blogCategory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magefan\Blog\Model\Attribute\Config $attributeConfig
     * @param Collection\Factory $collectionFactory
     */
    public function __construct(
        \Magefan\Blog\Model\ResourceModel\Category $blogCategory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    ) {
        $this->_blogCategory = $blogCategory;
        $this->_cache = $cache;
        $this->_storeManager = $storeManager;
        $this->_coreResource = $resource;
        parent::__construct(
            $resource->getConnection(),
            $resource->getTableName('magefan_blog_category'),
            [
                Dbp::ID_FIELD => 'category_id',
                Dbp::PATH_FIELD => 'path',
                Dbp::ORDER_FIELD => 'position',
                Dbp::LEVEL_FIELD => 'level'
            ]
        );
        $this->_eventManager = $eventManager;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Load tree
     *
     * @param   int|Node|string $parentNode
     * @param   int $recursionLevel
     * @return  $this
     */
    public function load($parentNode = null, $recursionLevel = 0)
    {
        if (!$this->_loaded) {
            $startLevel = 1;
            $parentPath = '';

            if ($parentNode instanceof Node) {
                $parentPath = $parentNode->getData($this->_pathField);
                $startLevel = $parentNode->getData($this->_levelField);
            } elseif (is_numeric($parentNode)) {
                $select = $this->_conn->select()
                    ->from($this->_table, [$this->_pathField, $this->_levelField])
                    ->where("{$this->_idField} = ?", $parentNode);
                $parent = $this->_conn->fetchRow($select);

                $startLevel = $parent[$this->_levelField];
                $parentPath = $parent[$this->_pathField];
                $parentNode = null;
            } elseif (is_string($parentNode)) {
                $parentPath = $parentNode;
                $startLevel = count(explode(',', $parentPath)) - 1;
                $parentNode = null;
            }

            $select = clone $this->_select;

            $select->order($this->_table . '.' . $this->_orderField . ' ASC');
            if ($parentPath) {
                $pathField = $this->_conn->quoteIdentifier([$this->_table, $this->_pathField]);

                $like = explode('/', $parentPath);
                array_pop($like);
                $like = implode('/', $like);

                $select->where("{$pathField} LIKE ?", "{$like}/%");
            }
            if ($recursionLevel != 0) {
                $levelField = $this->_conn->quoteIdentifier([$this->_table, $this->_levelField]);
                $select->where("{$levelField} <= ?", $startLevel + $recursionLevel);
            }

            $arrNodes = $this->_conn->fetchAll($select);

            $childrenItems = [];

            $dataRoot = [
                'category_id' => 0
            ];

            array_unshift($arrNodes, $dataRoot);

            foreach ($arrNodes as $nodeInfo) {
                if (!empty($nodeInfo['category_id'])) {
                    if (empty($nodeInfo['path'])) {
                        $nodeInfo['path'] = $nodeInfo['category_id'];
                    } else {
                        $nodeInfo['path'] .= '/'. $nodeInfo['category_id'];
                    }
                }

                $nodeInfo['post_count'] = $this->getCategoryPostsCount((int)$nodeInfo['category_id']);
                $nodeInfo['children_count'] = $this->getCategoryChildrenCount((int)$nodeInfo['category_id']);

                $pathToParent = explode('/', $nodeInfo[$this->_pathField] ?? '');
                array_pop($pathToParent);
                $pathToParent = implode('/', $pathToParent);

                if (isset($nodeInfo['level']) && $pathToParent == '') {
                    $pathToParent = '0';
                }

                $childrenItems[$pathToParent][] = $nodeInfo;
            }

            $this->addChildNodes($childrenItems, $parentPath, $parentNode);

            $this->_loaded = true;
        }

        return $this;
    }

    /**
     * Load ensured nodes
     *
     * @param object $category
     * @param Node $rootNode
     * @return void
     */
    public function loadEnsuredNodes($category, $rootNode)
    {
        $pathIds = $category->getPathIds();
        $rootNodeId = $rootNode->getId();
        $rootNodePath = $rootNode->getData($this->_pathField);

        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_orderField . ' ASC');

        if ($pathIds) {
            $condition = $this->_conn->quoteInto("{$this->_table}.{$this->_idField} in (?)", $pathIds);
            $select->where($condition);
        }

        $arrNodes = $this->_conn->fetchAll($select);

        if ($arrNodes) {
            $childrenItems = [];
            foreach ($arrNodes as $nodeInfo) {

                if (!empty($nodeInfo['category_id'])) {
                    if (empty($nodeInfo['path'])) {
                        $nodeInfo['path'] = $nodeInfo['category_id'];
                    } else {
                        $nodeInfo['path'] .= '/'. $nodeInfo['category_id'];
                    }
                }

                $nodeId = $nodeInfo[$this->_idField];
                if ($nodeId <= $rootNodeId) {
                    continue;
                }

                $pathToParent = explode('/', $nodeInfo[$this->_pathField] ?? '');
                array_pop($pathToParent);
                $pathToParent = implode('/', $pathToParent);

                $childrenItems[$pathToParent][] = $nodeInfo;
            }

            $this->_addChildNodes($childrenItems, $rootNodePath, $rootNode, true);
        }
    }

    /**
     * @param int $categoryId
     * @return int
     */
    private function getCategoryPostsCount(int $categoryId): int
    {
        if (null === $this->categoryPostsCount) {
            $select = $this->_conn->select()
                ->from(
                    $this->_coreResource->getTableName('magefan_blog_post_category'),
                    ['category_id', 'post_count' => new \Zend_Db_Expr('COUNT(post_id)')]
                )
                ->group('category_id');

            $this->categoryPostsCount = $this->_conn->fetchPairs($select);
        }

        return $this->categoryPostsCount[$categoryId] ?? 0;
    }

    /**
     * @param int $categoryId
     * @return int
     */
    private function getCategoryChildrenCount(int $categoryId): int
    {
        if (null === $this->categoryChildrenCount) {
            $tableName = $this->_coreResource->getTableName('magefan_blog_category');

            // Fetch all categories with their path
            $select = $this->_conn->select()
                ->from($tableName, ['category_id', 'path']);

            $rows = $this->_conn->fetchAll($select);

            $collectData = [];

            foreach ($rows as $row) {
                $path = (string)$row['path'];
                $parts = explode('/', $path);

                foreach ($parts as $level => $catId) {
                    if (isset($collectData[$level][$catId])) {
                        $collectData[$level][$catId]++;
                    } else {
                        $collectData[$level][$catId] = 0;
                    }
                }
            }

            foreach ($collectData as $level => $categories) {
                foreach ($categories as $catId => $count) {
                    if (isset($this->categoryChildrenCount[$catId])) {
                        $this->categoryChildrenCount[$catId] += $count;
                    } else {
                        $this->categoryChildrenCount[$catId] = $count;
                    }
                }
            }
        }

        return $this->categoryChildrenCount[$categoryId] ?? 0;
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return \Magefan\Blog\Model\ResourceModel\Category\Tree
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * @param $collection
     * @return $this
     */
    public function addCollectionData($collection = null)
    {
        if ($collection === null) {
            $collection = $this->getCollection();
        } else {
            $this->setCollection($collection);
        }

        $nodeIds = [];

        foreach ($this->getNodes() as $node) {
            $nodeIds[] = $node->getId();
        }

        $collection->addFieldToFilter('category_id', ['in' => $nodeIds]);

        return $this;
    }


    /**
     * Get categories collection
     *
     * @param boolean $sorted
     * @return Collection
     */
    public function getCollection($sorted = false)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    /**
     * Clean unneeded collection
     *
     * @param Collection|array $object
     * @return void
     */
    protected function _clean($object)
    {
        if (is_array($object)) {
            foreach ($object as $obj) {
                $this->_clean($obj);
            }
        }
        unset($object);
    }

    /**
     * Enter description here...
     *
     * @param Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        if ($this->_collection !== null) {
            $this->_clean($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param boolean $sorted
     * @return Collection
     */
    protected function _getDefaultCollection($sorted = false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = $this->_collectionFactory->create();
        $collection->addAttributeToSelect('title');

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }

        return $collection;
    }

    /**
     * Executing parents move method and cleaning cache after it
     *
     * @param mixed $category
     * @param mixed $newParent
     * @param mixed $prevNode
     * @return void
     */
    public function move($category, $newParent, $prevNode = null)
    {
        $this->_blogCategory->move($category->getId(), $newParent->getId());
        parent::move($category, $newParent, $prevNode);

        $this->_afterMove();
    }

    /**
     * Move tree after
     *
     * @return $this
     */
    protected function _afterMove()
    {
        $this->_cache->clean([\Magefan\Blog\Model\Category::CACHE_TAG]);
        return $this;
    }
}
