<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Blog post related posts block
 */
class RelatedPosts extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        $pageSize = (int) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_POSTS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->_postCollection = $this->getPost()->getRelatedPosts()
            ->addActiveFilter()
            ->setPageSize($pageSize ?: 5);

        $this->_postCollection->getSelect()->order('rl.position', 'ASC');
    }

    /**
     * Retrieve true if Display Related Posts enabled
     * @return boolean
     */
    public function displayPosts()
    {
        return (bool) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_POSTS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData(
                'post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }
}
