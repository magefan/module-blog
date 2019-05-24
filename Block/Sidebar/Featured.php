<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar categories block
 */
class Featured extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'featured_posts';

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addPostsFilter(
            $this->getPostIdsConfigValue()
        );
    }

    /**
     * Retrieve post ids string
     * @return string
     */
    protected function getPostIdsConfigValue()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/posts_ids',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
