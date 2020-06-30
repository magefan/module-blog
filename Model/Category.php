<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Category model
 *
 * @method \Magefan\Blog\Model\ResourceModel\Category _getResource()
 * @method \Magefan\Blog\Model\ResourceModel\Category getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(string $value)
 * @method $this setMetaDescription(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 * @method $this setUrlKey(string $value)
 * @method string getUrlKey()
 * @method $this setMetaTitle(string $value)
 * @method string getPath()
 * @method $this setPath($value)
 */
class Category extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    /**
     * blog cache category
     */
    const CACHE_TAG = 'mfb_c';

    /**
     * Category's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_blog_category';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'blog_category';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var array
     */
    private static $loadedCategoriesRepository = [];

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Url $url,
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_url = $url;
        $this->postCollectionFactory = $postCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\Blog\Model\ResourceModel\Category::class);
        $this->controllerName = URL::CONTROLLER_CATEGORY;
    }

    /**
     * Retrieve identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];

        if ($this->getId()) {
            $identities[] = self::CACHE_TAG . '_' . $this->getId();
        }

        return $identities;
    }

    /**
     * Load object data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     * @deprecated
     */
    public function load($modelId, $field = null)
    {
        $object = parent::load($modelId, $field);
        if (!isset(self::$loadedCategoriesRepository[$object->getId()])) {
            self::$loadedCategoriesRepository[$object->getId()] = $object;
        }

        return $object;
    }

    /**
     * Load category by id
     * @param  int $categoryId
     * @return self
     */
    private function loadFromRepository($categoryId)
    {
        if (!isset(self::$loadedCategoriesRepository[$categoryId])) {
            $category = clone $this;
            $category->unsetData();
            $category->load($categoryId);
            $categoryId = $category->getId();
        }

        return self::$loadedCategoriesRepository[$categoryId];
    }

    /**
     * Retrieve controller name
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Categories' : 'Category';
    }

    /**
     * Deprecated
     * Retrieve true if category is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getIsActive() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available category statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    /**
     * Check if category identifier exist for specific store
     * return category id if category exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Retrieve parent category ids
     * @return array
     */
    public function getParentIds()
    {
        $k = 'parent_ids';
        if (!$this->hasData($k)) {
            $this->setData(
                $k,
                $this->getPath() ? explode('/', $this->getPath()) : []
            );
        }

        return $this->getData($k);
    }

    /**
     * Retrieve parent category id
     * @return array
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        if ($parentIds) {
            return $parentIds[count($parentIds) - 1];
        }

        return 0;
    }

    /**
     * Retrieve parent category
     * @return self || false
     */
    public function getParentCategory()
    {
        $k = 'parent_category';
        if (null === $this->getData($k)) {
            $this->setData($k, false);
            if ($pId = $this->getParentId()) {
                $category = $this->loadFromRepository($pId);
                if ($category->getId()) {
                    if ($category->isVisibleOnStore($this->getStoreId())) {
                        $this->setData($k, $category);
                    }
                }
            }
        }

        return $this->getData($k);
    }

    /**
     * Check if current category is parent category
     * @param  self  $category
     * @return boolean
     */
    public function isParent($category)
    {
        if (is_object($category)) {
            $category = $category->getId();
        }

        return in_array($category, $this->getParentIds());
    }

    /**
     * Retrieve children category ids
     * @param  bool  $grandchildren
     * @return array
     */
    public function getChildrenIds($grandchildren = true)
    {
        $k = 'children_ids';
        if (!$this->hasData($k)) {
            $categories = \Magento\Framework\App\ObjectManager::getInstance()
                ->create($this->_collectionName);

            $allIds = $ids = [];
            foreach ($categories as $category) {
                if ($category->isParent($this)) {
                    $allIds[] = $category->getId();
                    if ($category->getLevel() == $this->getLevel() + 1) {
                        $ids[] = $category->getId();
                    }
                }
            }

            $this->setData('all_' . $k, $allIds);
            $this->setData($k, $ids);
        }

        return $this->getData(
            ($grandchildren ? 'all_' : '') . $k
        );
    }

    /**
     * Check if current category is child category
     * @param  self  $category
     * @return boolean
     */
    public function isChild($category)
    {
        return $category->isParent($this);
    }

    /**
     * Retrieve category depth level
     * @return int
     */
    public function getLevel()
    {
        return count($this->getParentIds());
    }

    /**
     * Retrieve catgegory url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, $this->controllerName);
    }

    /**
     * Retrieve category url
     * @return string
     */
    public function getCategoryUrl()
    {
        if (!$this->hasData('category_url')) {
            $url = $this->_url->getUrl($this, $this->controllerName);
            $this->setData('category_url', $url);
        }

        return $this->getData('category_url');
    }

    /**
     * Retrieve catgegory canonical url
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->_url->getCanonicalUrl($this);
    }

    /**
     * Retrieve meta title
     * @return string
     */
    public function getMetaTitle()
    {
        $title = $this->getData('meta_title');
        if (!$title) {
            $title = $this->getData('title');
        }

        return trim($title);
    }

    /**
     * Retrieve meta description
     * @return string
     */
    public function getMetaDescription()
    {
        $desc = $this->getData('meta_description');
        if (!$desc) {
            $desc = $this->getData('content');
        }

        $desc = strip_tags($desc);
        if (mb_strlen($desc) > 160) {
            $desc = mb_substr($desc, 0, 160);
        }

        return trim($desc);
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore($storeId)
    {
        return $this->getIsActive()
            && (null === $storeId || array_intersect([0, $storeId], $this->getStoreIds()));
    }

    /**
     * Retrieve number of posts in this category
     *
     * @return int
     */
    public function getPostsCount()
    {
        $key = 'posts_count';
        if (!$this->hasData($key)) {
            $posts = $this->postCollectionFactory->create()
                ->addActiveFilter()
                ->addStoreFilter($this->getStoreId())
                ->addCategoryFilter($this);

            $this->setData($key, (int)$posts->getSize());
        }

        return $this->getData($key);
    }

    /**
     * Prepare all additional data
     * @param  string $format
     * @return self
     * @deprecated replaced with getDynamicData
     */
    public function initDinamicData()
    {
        $keys = [
            'meta_description',
            'meta_title',
            'category_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace(
                '_',
                '',
                ucwords($key, '_')
            );
            $this->$method();
        }

        return $this;
    }

    /**
     * Prepare all additional data
     * @return array
     */
    public function getDynamicData()
    {
        $data = $this->getData();

        $keys = [
            'meta_description',
            'meta_title',
            'category_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace(
                '_',
                '',
                ucwords($key, '_')
            );
            $data[$key] = $this->$method();
        }

        return $data;
    }

    /**
     * Duplicate category and return new object
     * @return self
     */
    public function duplicate()
    {
        $object = clone $this;
        $object
            ->unsetData('category_id')
            ->unsetData('identifier')
            ->setTitle($object->getTitle() . ' (' . __('Duplicated') . ')')
            ->setData('is_active', 0);

        return $object->save();
    }
}
