<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Widget;

/**
 * Blog recent posts widget
 */
class Recent extends \Magefan\Blog\Block\Post\PostList\AbstractList implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;
    
     /**
     * @var \Magefan\Blog\Model\TagFactory
     */
    protected $tagFactory;

    /**
     * @var \Magefan\Blog\Model\Tag
     */
    protected $tag;

    /**
     * @var \Magefan\Blog\Model\AuthorFactory
     */
    protected $authorFactory;

    /**
     * @var \Magefan\Blog\Model\Author
     */
    protected $author;

    
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
     * @param \Magefan\Blog\Model\TagFactory $tagFactory
     * @param \Magefan\Blog\Model\AuthorFactory $authorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magefan\Blog\Model\Url $url,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magefan\Blog\Model\AuthorFactory $authorFactory,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $filterProvider, $postCollectionFactory, $url, $data);
        $this->_categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->authorFactory = $authorFactory;

    }

    /**
     * Set blog template
     *
     * @return this
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getData('custom_template') ?: 'Magefan_Blog::widget/recent.phtml'
        );

        return parent::_toHtml();
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?: __('Recent Blog Posts');
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

        if ($category = $this->getCategory()) {
            $this->_postCollection->addCategoryFilter($category);
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
                 if ($tag = $this->getTag()) {
            $this->_postCollection->addTagFilter($tag);
        }

        if ($author = $this->getAuthor()) {
            $this->_postCollection->addAuthorFilter($author);
        }

        if ($this->getData('from')) {
            $this->_postCollection
                ->addFieldToFilter('publish_time', array('gteq' => $this->getData('from') . " 00:00:00"));
        }

        if ($this->getData('to')) {
            $this->_postCollection
                ->addFieldToFilter('publish_time', array('lteq' => $this->getData('to') . " 00:00:00"));
        }
    }


    /**
     * Retrieve author instance
     *
     * @return \Magefan\Blog\Model\Author
     */
    public function getAuthor()
    {
        if ($this->author === null) {
            if ($authotId = $this->getData('author_id')) {
                $author = $this->authorFactory->create();
                $author->load($authotId);

                return $this->author = $author;

            }

            $this->author = false;
        }

        return $this->author;

    }

    /**
     * Retrieve tag instance
     *
     * @return \Magefan\Blog\Model\Tag
     */
    public function getTag()
    {
        if ($this->tag === null) {
            if ($tagId = $this->getData('tags_id')) {

                $tag = $this->tagFactory->create();
                $tag->load($tagId);

                return $this->tag = $tag;

            }

            $this->tag = false;
        }

        return $this->tag;

            }

            $this->_category = false;
        }

        return $this->_category;
    }

    /**
     * Retrieve post short content
     * @param  \Magefan\Blog\Model\Post $post
     *
     * @return string
     */
    public function getShorContent($post)
    {
        return $post->getShortFilteredContent();
    }
}
