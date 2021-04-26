<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block;

use Magento\Framework\Api\SortOrder;
use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Model\Config\Source\PostsSortBy;

/**
 * Blog index block
 */
class Index extends \Magefan\Blog\Block\Post\PostList
{
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set(
            $this->_getConfigValue('meta_title') ?: $this->_getConfigValue('title')
        );
        $this->pageConfig->setKeywords($this->_getConfigValue('meta_keywords'));
        $this->pageConfig->setDescription($this->_getConfigValue('meta_description'));

        if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_INDEX)) {
            $this->pageConfig->addRemotePageAsset(
                $this->_url->getBaseUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(
                $this->escapeHtml($this->_getConfigValue('title'))
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Toolbar Block
     * @return \Magefan\Blog\Block\Post\PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        $toolBarBlock = parent::getToolbarBlock();
        $limit = (int)$this->_scopeConfig->getValue(
            'mfblog/index_page/posts_per_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($limit) {
            $toolBarBlock->setData('limit', $limit);
        }

        return $toolBarBlock;
    }


    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();

        $displayMode = $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE
        );
        /* If featured posts enabled */
        if ($displayMode == 1) {
            $postIds = $this->_scopeConfig->getValue(
                \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_FEATURED_POST_IDS,
                ScopeInterface::SCOPE_STORE
            );
            $this->_postCollection->addPostsFilter($postIds);
        } else {
            $this->_postCollection->addRecentFilter();
        }
    }

     /**
      * Retrieve collection order field
      *
      * @return string
      */
    public function getCollectionOrderField()
    {
        $postsSortBy = $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_POSTS_SORT_BY,
            ScopeInterface::SCOPE_STORE
        );

        switch ($postsSortBy) {
            case PostsSortBy::POSITION:
                return self::POSTS_SORT_FIELD_BY_POSITION;
            case PostsSortBy::TITLE:
                return self::POSTS_SORT_FIELD_BY_TITLE;
            default:
                return parent::getCollectionOrderField();
        }
    }

    /**
     * Retrieve collection order direction
     *
     * @return string
     */
    public function getCollectionOrderDirection()
    {
        $postsSortBy = $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_POSTS_SORT_BY,
            ScopeInterface::SCOPE_STORE
        );

        if (PostsSortBy::TITLE == $postsSortBy) {
            return SortOrder::SORT_ASC;
        }
        return parent::getCollectionOrderDirection();
    }

    /**
     * Retrieve blog title
     * @return string
     */
    protected function _getConfigValue($param)
    {
        return $this->_scopeConfig->getValue(
            'mfblog/index_page/'.$param,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @param  string $key
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title = null, $key = null)
    {
        if ($breadcrumbsBlock = $this->getBreadcrumbsBlock()) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $blogTitle = $this->_scopeConfig->getValue(
                'mfblog/index_page/title',
                ScopeInterface::SCOPE_STORE
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __($blogTitle),
                    'title' => __($blogTitle),
                    'link' => null,
                ]
            );
        }
    }

    /**
     * Get template type
     *
     * @return string
     */
    public function getPostTemplateType()
    {
        $template = (string)$this->_scopeConfig->getValue(
            'mfblog/index_page/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template) {
            return $template;
        }

        return parent::getPostTemplateType();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $displayMode = $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE
        );
        if (2 == $displayMode) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Retrieve identities
     *git add
     * @return array
     */
    public function getIdentities()
    {
        $displayMode = $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_PATH_HOMEPAGE_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE
        );
        if (2 == $displayMode) {
            return [];
        }
        return parent::getIdentities();
    }
}
