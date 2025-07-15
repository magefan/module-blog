<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Widget;

use Magefan\Blog\Block\Post\PostList\AbstractList;
use Magefan\Blog\Model\Config\Source\PostsSortBy;
use Magento\Framework\Api\SortOrder;

/**
 * Blog recent posts widget
 */
class Recent extends AbstractList implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var array
     */
    static $processedIds = [];

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magefan\Blog\Model\Category
     */
    protected $_category;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magefan\Blog\Model\Url $url,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $filterProvider, $postCollectionFactory, $url, $data);
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Set blog template
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getData('custom_template') ?: 'Magefan_Blog::widget/recent.phtml'
        );

        foreach ($this->getPostCollection() as $item) {
            self::$processedIds[$item->getId()] = $item->getId();
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?: '';
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        $size = $this->getData('number_of_posts');
        if (!$size) {
            $size = (int) $this->_scopeConfig->getValue(
                'mfblog/sidebar/recent_posts/posts_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        $this->setPageSize($size);

        parent::_preparePostCollection();

        $this->_postCollection->addRecentFilter();

        $categoryIds = explode(',', (string)$this->getData('category_id'));
        if (count($categoryIds) > 1) {
            $this->_postCollection->addCategoryFilter($categoryIds);
        } elseif ($category = $this->getCategory()) {
            $this->_postCollection->addCategoryFilter($category);
        }

        if ($tagId = $this->getData('tag_id')) {
            $this->_postCollection->addTagFilter($tagId);
        }

        if ($authorId = $this->getData('author_id')) {
            $this->_postCollection->addAuthorFilter($authorId);
        }

        if ($from = $this->getData('from')) {
            $this->_postCollection
                ->addFieldToFilter('publish_time', ['gteq' => $from . " 00:00:00"]);
        }

        if ($to = $this->getData('to')) {
            $this->_postCollection
                ->addFieldToFilter('publish_time', ['lteq' => $to . " 00:00:00"]);
        }

        $enableNoRepeat = $this->getData('no_repeat_posts_enable');
        if ($enableNoRepeat && self::$processedIds) {
            $this->_postCollection->addFieldToFilter('post_id', ['nin' => self::$processedIds]);
        }
    }

    /**
     * Retrieve category instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getCategory()
    {
        if ($this->_category === null) {
            if ($categoryId = $this->getData('category_id')) {
                $category = $this->_categoryFactory->create();
                $category->load($categoryId);

                $storeId = $this->_storeManager->getStore()->getId();
                if ($category->isVisibleOnStore($storeId)) {
                    $category->setStoreId($storeId);
                    return $this->_category = $category;
                }
            }

            $this->_category = false;
        }

        return $this->_category;
    }
    
    /**
     * Retrieve post short content
     *
     * @param  \Magefan\Blog\Model\Post $post
     * @param  mixed $len
     * @param  mixed $endCharacters
     * @return string
     */
    public function getShorContent($post, $len = null, $endCharacters = null)
    {
        return $post->getShortFilteredContent($len, $endCharacters);
    }

    /**
     * @return string
     */
    public function getCollectionOrderField(): string
    {
        $postsSortBy = (int)$this->getData('posts_sort_by');
        if ($postsSortBy) {
            switch ($postsSortBy) {
                case PostsSortBy::POSITION:
                    return AbstractList::POSTS_SORT_FIELD_BY_POSITION;
                case PostsSortBy::TITLE:
                    return AbstractList::POSTS_SORT_FIELD_BY_TITLE;
            }
        }

        return parent::getCollectionOrderField();
    }

    /**
     * Retrieve collection order direction
     *
     * @return string
     */
    public function getCollectionOrderDirection()
    {
        $postsSortBy = (int)$this->getData('posts_sort_by');

        if (PostsSortBy::TITLE == $postsSortBy) {
            return SortOrder::SORT_ASC;
        }

        return parent::getCollectionOrderDirection();
    }
}
