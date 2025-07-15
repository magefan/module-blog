<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\PostList;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SortOrder;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\DataObject\IdentityInterface;

/**
 * Abstract blog post list block
 */
abstract class AbstractList extends Template implements IdentityInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_post;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $_postCollectionFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post\Collection
     */
    protected $_postCollection;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * @var \Magefan\Blog\Model\Config
     */
    protected $config;

    /**
     * @var \Magefan\Blog\Model\TemplatePool
     */
    protected $templatePool;

    const POSTS_SORT_FIELD_BY_PUBLISH_TIME = 'main_table.publish_time';
    const POSTS_SORT_FIELD_BY_POSITION = 'position';
    const POSTS_SORT_FIELD_BY_TITLE = 'main_table.title';

    /**
     * AbstractList constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magefan\Blog\Model\Url $url
     * @param array $data
     * @param null $config
     * @param null $templatePool
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magefan\Blog\Model\Url $url,
        array $data = [],
        $config = null,
        $templatePool = null
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_postCollectionFactory = $postCollectionFactory;
        $this->_url = $url;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->config = $config ?: $objectManager->get(
            \Magefan\Blog\Model\Config::class
        );
        $this->templatePool = $templatePool ?: $objectManager->get(
            \Magefan\Blog\Model\TemplatePool::class
        );
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        $this->_postCollection = $this->_postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder($this->getCollectionOrderField(), $this->getCollectionOrderDirection());

        if ($this->getPageSize()) {
            $this->_postCollection->setPageSize($this->getPageSize());
        }
    }

    /**
     * Retrieve collection order field
     *
     * @return string
     */
    public function getCollectionOrderField()
    {
        return self::POSTS_SORT_FIELD_BY_PUBLISH_TIME;
    }

    /**
     * Retrieve collection order direction
     *
     * @return string
     */
    public function getCollectionOrderDirection()
    {
        return SortOrder::SORT_DESC;
    }

    /**
     * Prepare posts collection
     *
     * @return \Magefan\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection()
    {
        if (null === $this->_postCollection) {
            $this->_preparePostCollection();
        }

        return $this->_postCollection;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_EXTENSION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        $identities[] = \Magefan\Blog\Model\Post::CACHE_TAG . '_' . 0;
        foreach ($this->getPostCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        return array_unique($identities);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            [$this->getNameInLayout()]
        );
    }

    /**
     * Retrieve 1 if display author information is enabled
     * @return int
     */
    public function authorEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 1 if author page is enabled
     * @return int
     */
    public function authorPageEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/page_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve true if magefan comments are enabled
     * @return bool
     */
    public function magefanCommentsEnabled()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == \Magefan\Blog\Model\Config\Source\CommetType::MAGEFAN;
    }

    /**
     * @return bool
     */
    public function viewsCountEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/post_view/views_count/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magefan\Blog\ViewModel\Style
     */
    public function getStyleViewModel()
    {
        $viewModel = $this->getData('style_view_model');
        if (!$viewModel) {
            $viewModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\Blog\ViewModel\Style::class);
            $this->setData('style_view_model', $viewModel);
        }

        return $viewModel;
    }

    /**
     * @return string
     */
    public function getPageParamName()
    {
        return $this->config->getPagePaginationType() !== 'p' ? 'page' : 'p';
    }

    /**
     * Retrieve 1 if display reading time is enabled
     * @return int
     */
    public function readingTimeEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/post_view/reading_time/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
