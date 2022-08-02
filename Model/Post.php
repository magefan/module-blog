<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Model\Url;
use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Api\ShortContentExtractorInterface;

/**
 * Post model
 *
 * @method \Magefan\Blog\Model\ResourceModel\Post _getResource()
 * @method \Magefan\Blog\Model\ResourceModel\Post getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(string $value)
 * @method $this setMetaDescription(string $value)
 * @method $this setIdentifier(string $value)
 * @method string getContent()
 * @method string getShortContent()
 * @method $this setContent(string $value)
 * @method string getContentHeading()
 * @method $this setContentHeading(string $value)
 */
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Posts's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * blog cache post
     */
    const CACHE_TAG = 'mfb_p';

    /**
     * Gallery images separator constant
     */
    const GALLERY_IMAGES_SEPARATOR = ';';

    /**
     * Base media folder path
     */
    const BASE_MEDIA_PATH = 'magefan_blog';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_blog_post';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'blog_post';

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $random;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * @var \Magefan\Blog\Api\AuthorInterfaceFactory
     */
    protected $_authorFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\Collection
     */
    protected $_parentCategories;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Tag\Collection
     */
    protected $_relatedTags;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    protected $comments;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post\Collection
     */
    protected $_relatedPostsCollection;

    /**
     * @var \Magefan\Blog\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var ShortContentExtractorInterface
     */
    protected $shortContentExtractor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magefan\Blog\Api\AuthorInterfaceFactory $authorFactory
     * @param \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Math\Random $random,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Url $url,
        \Magefan\Blog\Model\ImageFactory $imageFactory,
        \Magefan\Blog\Api\AuthorInterfaceFactory $authorFactory,
        \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        \Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->filterProvider = $filterProvider;
        $this->random = $random;
        $this->scopeConfig = $scopeConfig;
        $this->_url = $url;
        $this->imageFactory = $imageFactory;
        $this->_authorFactory = $authorFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_relatedPostsCollection = clone($this->getCollection());
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\Blog\Model\ResourceModel\Post::class);
        $this->controllerName = URL::CONTROLLER_POST;
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

        $oldCategories = $this->getOrigData('categories');
        if (!is_array($oldCategories)) {
            $oldCategories = [];
        }

        $newCategories = $this->getData('categories');
        if (!is_array($newCategories)) {
            $newCategories = [];
        }

        if ($this->getData('is_active') && $this->getData('is_active') != $this->getOrigData('is_active')) {
            $identities[] = self::CACHE_TAG . '_' . 0;
        }

        $isChangedCategories = count(array_diff($oldCategories, $newCategories));

        if ($isChangedCategories) {
            $changedCategories = array_unique(
                array_merge($oldCategories, $newCategories)
            );
            foreach ($changedCategories as $categoryId) {
                $identities[] = \Magefan\Blog\Model\Category::CACHE_TAG . '_' . $categoryId;
            }
        }

        $links = $this->getData('links');
        if (!empty($links['product'])) {
            foreach ($links['product'] as $productId => $linkData) {
                $identities[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $productId;
            }
        }

        return $identities;
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return (string)$this->getData('identifier');
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
        return $plural ? 'Posts' : 'Post';
    }

    /**
     * Deprecated
     * Retrieve true if post is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getIsActive() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available post statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
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
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Retrieve post url path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, $this->controllerName);
    }

    /**
     * Retrieve post url
     * @return string
     */
    public function getPostUrl()
    {
        if (!$this->hasData('post_url')) {
            $url = $this->_url->getUrl($this, $this->controllerName);
            $this->setData('post_url', $url);
        }

        return $this->getData('post_url');
    }

    /**
     * Retrieve post canonical url
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->_url->getCanonicalUrl($this);
    }

    /**
     * Retrieve featured image url
     * @return string
     */
    public function getFeaturedImage()
    {
        if (!$this->hasData('featured_image')) {
            if ($file = $this->getData('featured_img')) {
                $image = $this->_url->getMediaUrl($file);
            } else {
                $image = false;
            }
            $this->setData('featured_image', $image);
        }

        return $this->getData('featured_image');
    }

    /**
     * Retrieve featured link image url
     * @return mixed
     */
    public function getFeaturedListImage()
    {
        if (!$this->hasData('featured_list_image')) {
            if ($file = $this->getData('featured_list_img')) {
                $image = $this->_url->getMediaUrl($file);
            } else {
                $image = false;
            }
            $this->setData('featured_list_image', $image);
        }

        return $this->getData('featured_list_image');
    }

    /**
     * Set media gallery images url
     *
     * @param array $images
     * @return this
     */
    public function setGalleryImages(array $images)
    {
        $this->setData(
            'media_gallery',
            implode(
                self::GALLERY_IMAGES_SEPARATOR,
                $images
            )
        );

        /* Reinit Media Gallery Images */
        $this->unsetData('gallery_images');
        $this->getGalleryImages();

        return $this;
    }

    /**
     * Retrieve media gallery images url
     * @return string
     */
    public function getGalleryImages()
    {
        if (!$this->hasData('gallery_images')) {
            $images = [];
            $gallery = $this->getData('media_gallery');
            if ($gallery && !is_array($gallery)) {
                $gallery = explode(
                    self::GALLERY_IMAGES_SEPARATOR,
                    $gallery
                );
            }
            if (!empty($gallery)) {
                foreach ($gallery as $file) {
                    if ($file) {
                        $images[] = $this->imageFactory->create()
                            ->setFile($file);
                    }
                }
            }
            $this->setData('gallery_images', $images);
        }

        return $this->getData('gallery_images');
    }

    /**
     * Retrieve first image url
     * @return string
     */
    public function getFirstImage()
    {
        if (!$this->hasData('first_image')) {
            $image = $this->getFeaturedImage();
            if (!$image) {
                $content = $this->getFilteredContent();
                $match = null;
                preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $match);
                if (!empty($match['src'])) {
                    $image = $match['src'];
                }
            }
            $this->setData('first_image', $image);
        }

        return $this->getData('first_image');
    }

    /**
     * Retrieve filtered content
     *
     * @return string
     */
    public function getFilteredContent()
    {
        $key = 'filtered_content';
        if (!$this->hasData($key)) {
            $content = $this->filterProvider->getPageFilter()->filter(
                (string) $this->getContent() ?: ''
            );
            $this->setData($key, $content);
        }
        return $this->getData($key);
    }

    /**
     * Retrieve short filtered content
     * @param  mixed $len
     * @param  mixed $endСharacters
     * @return string
     */
    public function getShortFilteredContent($len = null, $endСharacters = null)
    {
        /* Fix for custom themes that send wrong parameters to this function, and that brings the error */
        if (is_object($len)) {
             $len = null;
        }
        /* End fix */

        $key = 'short_filtered_content' . $len;
        if (!$this->hasData($key)) {

            if ($this->getShortContent()) {
                $content = (string)$this->getShortContent() ?: '';
            } else {
                //$content = $this->getFilteredContent();
                $content = (string)$this->getContent() ?: '';
            }

            $content = $this->getShortContentExtractor()->execute($content, $len, $endСharacters);

            $this->setData($key, $content);
        }

        return $this->getData($key);
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

        $key = 'filtered_meta_description';
        if (!$this->hasData($key)) {
            $desc = $this->getData('meta_description');
            if (!$desc) {
                $desc = $this->getShortFilteredContent();
                $desc = str_replace(['<p>', '</p>'], [' ', ''], $desc);
            }

            $desc = strip_tags($desc);
            if (mb_strlen($desc) > 200) {
                $desc = mb_substr($desc, 0, 200);
            }

            $desc = trim($desc);

            $this->setData($key, $desc);
        }

        return $this->getData($key);
    }

    /**
     * Retrieve og title
     * @return string
     */
    public function getOgTitle()
    {
        $title = $this->getData('og_title');
        if (!$title) {
            $title = $this->getMetaTitle();
        }

        return trim($title);
    }

    /**
     * Retrieve og description
     * @return string
     */
    public function getOgDescription()
    {
        $desc = $this->getData('og_description');
        if (!$desc) {
            $desc = $this->getMetaDescription();
        } else {
            $desc = strip_tags($desc);
            if (mb_strlen($desc) > 300) {
                $desc = mb_substr($desc, 0, 300);
            }
        }

        return trim(html_entity_decode($desc));
    }

    /**
     * Retrieve og type
     * @return string
     */
    public function getOgType()
    {
        $type = $this->getData('og_type');
        if (!$type) {
            $type = 'article';
        }

        return trim($type);
    }

    /**
     * Retrieve og image url
     * @return string
     */
    public function getOgImage()
    {
        if (!$this->hasData('og_image')) {
            if ($file = $this->getData('og_img')) {
                $image = $this->_url->getMediaUrl($file);
            } else {
                $image = false;
            }
            $this->setData('og_image', $image);
        }

        return $this->getData('og_image');
    }

    /**
     * Retrieve post parent categories
     * @return \Magefan\Blog\Model\ResourceModel\Category\Collection
     */
    public function getParentCategories()
    {
        if (null === $this->_parentCategories) {
            $this->_parentCategories = $this->_categoryCollectionFactory->create()
                ->addFieldToFilter('category_id', ['in' => $this->getCategories()])
                ->addStoreFilter($this->getStoreId())
                ->addActiveFilter()
                ->setOrder('position');
        }

        return $this->_parentCategories;
    }

    /**
     * Retrieve parent category
     * @return \Magefan\Blog\Model\Category || false
     */
    public function getParentCategory()
    {
        $k = 'parent_category';
        if (null === $this->getData($k)) {
            $this->setData($k, false);
            foreach ($this->getParentCategories() as $category) {
                if ($category->isVisibleOnStore($this->getStoreId())) {
                    $this->setData($k, $category);
                    break;
                }
            }
        }

        return $this->getData($k);
    }

    /**
     * Retrieve post parent categories count
     * @return int
     */
    public function getCategoriesCount()
    {
        return count($this->getParentCategories());
    }

    /**
     * Retrieve post tags
     * @return \Magefan\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getRelatedTags()
    {
        if (null === $this->_relatedTags) {
            $this->_relatedTags = $this->_tagCollectionFactory->create()
                ->addFieldToFilter('tag_id', ['in' => $this->getTags()])
                ->addStoreFilter($this->getStoreId())
                ->addActiveFilter()
                ->setOrder('title');
        }

        return $this->_relatedTags;
    }

    /**
     * Retrieve post tags count
     * @return int
     */
    public function getTagsCount()
    {
        return count($this->getRelatedTags());
    }

    /**
     * Retrieve post comments
     * @param  boolean $active
     * @return \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    public function getComments($active = true)
    {
        if (null === $this->comments) {
            $this->comments = $this->_commentCollectionFactory->create()
                ->addFieldToFilter('post_id', $this->getId());
        }

        return $this->comments;
    }

    /**
     * Retrieve active comments count
     * @return int
     */
    public function getCommentsCount()
    {
        $enableComments = $this->getEnableComments();
        if ($enableComments || $enableComments === null) {
            /*
            if (!$this->hasData('comments_count')) {
                $comments = $this->_commentCollectionFactory->create()
                    ->addFieldToFilter('post_id', $this->getId())
                    ->addActiveFilter()
                    ->addFieldToFilter('parent_id', 0);
                $this->setData('comments_count', (int)$comments->getSize());
            }
            */
            return $this->getData('comments_count');
        } else {
            return 0;
        }
    }

    /**
     * Retrieve post related posts
     * @return \Magefan\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPosts()
    {
        if (!$this->hasData('related_posts')) {
            $collection = $this->_relatedPostsCollection
                ->addFieldToFilter('post_id', ['neq' => $this->getId()])
                ->addStoreFilter($this->getStoreId());
            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('magefan_blog_post_relatedpost')],
                'main_table.post_id = rl.related_id',
                ['position']
            )->where(
                'rl.post_id = ?',
                $this->getId()
            );
            $this->setData('related_posts', $collection);
        }
        return $this->getData('related_posts');
    }

    /**
     * Retrieve post related products
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getRelatedProducts()
    {
        if (!$this->hasData('related_products')) {
            $collection = $this->_productCollectionFactory->create();

            if ($this->getStoreId()) {
                $collection->addStoreFilter($this->getStoreId());
            }

            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('magefan_blog_post_relatedproduct')],
                'e.entity_id = rl.related_id',
                ['position']
            )->where(
                'rl.post_id = ?',
                $this->getId()
            );

            $this->setData('related_products', $collection);
        }

        return $this->getData('related_products');
    }

    /**
     * Retrieve post author
     * @return \Magefan\Blog\Api\AuthorInterface | false
     */
    public function getAuthor()
    {
        if (!$this->hasData('author')) {
            $author = false;
            if ($authorId = $this->getData('author_id')) {
                $_author = $this->_authorFactory->create();
                $_author->load($authorId);

                if ($_author->getId() && $_author->isVisibleOnStore($this->getStoreId())) {
                    $author = $_author;
                }
            }
            $this->setData('author', $author);
        }
        return $this->getData('author');
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore($storeId)
    {
        return $this->getIsActive()
            && $this->getData('publish_time') <= $this->getResource()->getDate()->gmtDate()
            && (null === $storeId || array_intersect([0, $storeId], $this->getStoreIds()));
    }

    /**
     * Retrieve if is preview secret is valid
     * @return bool
     */
    public function isValidSecret($secret)
    {
        return ($secret && $this->getSecret() === $secret);
    }

    /**
     * Retrieve post publish date using format
     * @param  string $format
     * @return string
     */
    public function getPublishDate($format = '')
    {
        if (!$format) {
            $format = $this->scopeConfig->getValue(
                'mfblog/design/format_date',
                ScopeInterface::SCOPE_STORE
            );

            if (!$format) {
                $format = 'Y-m-d H:i:s';
            }
        }

        return \Magefan\Blog\Helper\Data::getTranslatedDate(
            $format,
            $this->getData('publish_time')
        );
    }

    /**
     * Retrieve true if post publish date display is enabled
     * @return bool
     */
    public function isPublishDateEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'mfblog/design/publication_date',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve post publish date using format
     * @param  string $format
     * @return string
     */
    public function getUpdateDate($format = 'Y-m-d H:i:s')
    {
        return \Magefan\Blog\Helper\Data::getTranslatedDate(
            $format,
            $this->getData('update_time')
        );
    }

    /**
     * Temporary method to get images from some custom blog version. Do not use this method.
     * @param  string $format
     * @return string
     */
    public function getPostImage()
    {
        $image = $this->getData('featured_img');
        if (!$image) {
            $image = $this->getData('post_image');
        }
        return $image;
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
            'og_image',
            'og_type',
            'og_description',
            'og_title',
            'meta_description',
            'meta_title',
            'short_filtered_content',
            'filtered_content',
            'first_image',
            'featured_image',
            'post_url',
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
     * @deprecated use getDynamicData method in graphQL data provider
     * Prepare all additional data
     * @param null|array $fields
     * @return array
     */
    public function getDynamicData($fields = null)
    {
        $data = $this->getData();

        $keys = [
            'og_image',
            'og_type',
            'og_description',
            'og_title',
            'meta_description',
            'meta_title',
            'short_filtered_content',
            'filtered_content',
            'first_image',
            'featured_image',
            'post_url',
        ];

        foreach ($keys as $key) {
            if (null === $fields || array_key_exists($key, $fields)) {
                $method = 'get' . str_replace(
                    '_',
                    '',
                    ucwords($key, '_')
                );
                $data[$key] = $this->$method();
            }
        }

        if (null === $fields || array_key_exists('tags', $fields)) {
            $tags = [];
            foreach ($this->getRelatedTags() as $tag) {
                $tags[] = $tag->getDynamicData(
                    // isset($fields['tags']) ? $fields['tags'] : null
                );
            }
            $data['tags'] = $tags;
        }

        /* Do not use check for null === $fields here
         * this checks is used for REST, and related data was not provided via reset */
        if (is_array($fields) && array_key_exists('related_posts', $fields)) {
            $relatedPosts = [];
            foreach ($this->getRelatedPosts() as $relatedPost) {
                $relatedPosts[] = $relatedPost->getDynamicData(
                    isset($fields['related_posts']) ? $fields['related_posts'] : null
                );
            }
            $data['related_posts'] = $relatedPosts;
        }

        /* Do not use check for null === $fields here */
        if (is_array($fields) && array_key_exists('related_products', $fields)) {
            $relatedProducts = [];
            foreach ($this->getRelatedProducts() as $relatedProduct) {
                $relatedProducts[] = $relatedProduct->getSku();
            }
            $data['related_products'] = $relatedProducts;
        }

        if (null === $fields || array_key_exists('categories', $fields)) {
            $categories = [];
            foreach ($this->getParentCategories() as $category) {
                $categories[] = $category->getDynamicData(
                    isset($fields['categories']) ? $fields['categories'] : null
                );
            }
            $data['categories'] = $categories;
        }

        if (null === $fields || array_key_exists('author', $fields)) {
            if ($author = $this->getAuthor()) {
                $data['author'] = $author->getDynamicData(
                    //isset($fields['author']) ? $fields['author'] : null
                );
            }
        }

        return $data;
    }

    /**
     * Duplicate post and return new object
     * @return self
     */
    public function duplicate()
    {
        $object = clone $this;
        $object
            ->unsetData('post_id')
            ->unsetData('creation_time')
            ->unsetData('update_time')
            ->unsetData('publish_time')
            ->unsetData('identifier')
            ->unsetData('comments_count')
            ->setTitle($object->getTitle() . ' (' . __('Duplicated') . ')')
            ->setData('is_active', 0);

        $relatedProductIds = $this->getRelatedProducts()->getAllIds();
        $relatedPpostIds = $this->getRelatedPosts()->getAllIds();

        $object->setData(
            'links',
            [
                'product' => array_combine($relatedProductIds, $relatedProductIds),
                'post' => array_combine($relatedPpostIds, $relatedPpostIds),
            ]
        );

        return $object->save();
    }

    /**
     * Retrieve secret key of post, it can be used during preview
     * @return string
     */
    public function getSecret()
    {
        if ($this->getId() && !$this->getData('secret')) {
            $this->setData(
                'secret',
                $this->random->getRandomString(32)
            );
            $this->save();
        }

        return $this->getData('secret');
    }

    /**
     * Retrieve updated at time
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData('update_time');
    }

    /**
     * @return ShortContentExtractorInterface
     */
    public function getShortContentExtractor()
    {
        if (null === $this->shortContentExtractor) {
            $this->shortContentExtractor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ShortContentExtractorInterface::class);
        }

        return $this->shortContentExtractor;
    }
}
