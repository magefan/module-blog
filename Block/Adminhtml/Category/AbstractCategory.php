<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Category;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\Store;
use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory;


/**
 * Class AbstractCategory
 */
class AbstractCategory extends \Magento\Backend\Block\Template
{

    protected $currentCategory = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\Tree
     */
    protected $_categoryTree;

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    protected $collectionFactory;

    /**
     * @var bool
     */
    protected $_withProductCount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magefan\Blog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magefan\Blog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_categoryTree = $categoryTree;
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_withProductCount = true;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getCategory()
    {
        if (null === $this->currentCategory) {
            $categoryId = (int)$this->getRequest()->getParam('id');

            if ($categoryId) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $catRepo = $objectManager->create( \Magefan\Blog\Api\CategoryRepositoryInterface::class);
                $this->currentCategory = $catRepo->getById($categoryId);
            }
        }

        return $this->currentCategory;
    }

    /**
     * Get category id
     *
     * @return int|string|null
     */
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }

        return \Magefan\Blog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * Get category path
     *
     * @return mixed
     */
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }

        return \Magefan\Blog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * Check store root category
     *
     * @return bool
     */
    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Get store from request
     *
     * @return Store
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Get root category for tree
     *
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }

        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            /*$storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Magefan\Blog\Model\Category::TREE_ROOT_ID;
            }*/
            $rootId = \Magefan\Blog\Model\Category::TREE_ROOT_ID;

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root) {
                $root->setIsVisible(true);
                if ($root->getId() == \Magefan\Blog\Model\Category::TREE_ROOT_ID) {
                    $root->setName(__('Root'));
                }
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * Get Default Store Id
     *
     * @return int
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * Get category collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->collectionFactory->create();

            $collection
                ->addFieldToSelect('category_id')
                ->addFieldToSelect('title')
                ->addFieldToSelect('is_active');

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get category node for tree
     *
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->_categoryTree->loadNode($nodeId);

        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Magefan\Blog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Magefan\Blog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());
        return $node;
    }

    /**
     * Get category save url
     *
     * @param array $args
     * @return string
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false, 'store' => $this->getStore()->getId()];
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/save', $params);
    }

    /**
     * Get category edit url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'blog/category/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        return  [\Magefan\Blog\Model\Category::TREE_ROOT_ID];
    }
}
