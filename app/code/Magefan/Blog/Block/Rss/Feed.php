<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
    /**
     * Retrieve rss feed url 
     * @return string
     */
    public function getLink()
    {
        return $this->getUrl('blog/rss/feed');
    }

    /**
     * Retrieve rss feed title 
     * @return string
     */
    public function getTitle()
    {
    	 return $this->_scopeConfig->getValue('mfblog/rss_feed/title', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve rss feed description 
     * @return string
     */
    public function getDescription()
    {
    	 return $this->_scopeConfig->getValue('mfblog/rss_feed/description', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve block identities
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_blog_rss_feed'  ];
    }

}
