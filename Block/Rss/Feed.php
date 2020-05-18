<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Rss;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog ree feed block
 */
class Feed extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    /*
     * Collection page size
     */
    const PAGE_SIZE = 10;

    /**
     * Retrieve rss feed url
     * @return string
     */
    public function getLink()
    {
        return $this->_url->getUrl('feed', 'rss');
    }

    /**
     * Retrieve rss feed title
     * @return string
     */
    public function getTitle()
    {
         return $this->_scopeConfig->getValue('mfblog/sidebar/rss_feed/title', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve rss feed description
     * @return string
     */
    public function getDescription()
    {
         return $this->_scopeConfig->getValue('mfblog/sidebar/rss_feed/description', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve rss feed collection size
     * @return string
     */
    public function getPageSize()
    {
        return $this->getData('page_size') ?: self::PAGE_SIZE;
    }

    /**
     * Retrieve post filtered content
     * @param  \Magefan\Blog\Model\Post $post
     * @return string
     */
    public function getPostContent($post)
    {
        $content = $post->getFilteredContent();
        /* Remove iframes */
        $content = preg_replace('/<iframe.*?\/iframe>/i', '', $content);
        /* Remove script */
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);

        /* Remove style tags */
        $content = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $content);

        return $content;
    }
    

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $categoryId = (int)$this->getRequest()->getParam('category_id');
        if ($categoryId) {
            $this->_postCollection->addCategoryFilter($categoryId);
        }
    }
}
