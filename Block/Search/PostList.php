<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Search;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog search result block
 */
class PostList extends \Magefan\Blog\Block\Post\PostList
{
	/**
	 * Retrieve query
	 * @return string
	 */
    public function getQuery()
    {
        return urldecode($this->getRequest()->getParam('q'));
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addSearchFilter(
            $this->getQuery()
        );
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->_getTitle();
        $this->_addBreadcrumbs($title, 'blog_search');
        $this->pageConfig->getTitle()->set($title);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve title
     * @return string
     */
    protected function _getTitle()
    {
        return __('Search "%1"', $this->getQuery());
    }

}
