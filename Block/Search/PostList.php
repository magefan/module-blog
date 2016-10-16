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

        $q = $this->getQuery();
        $this->_postCollection->addFieldToFilter(
            ['title', 'content_heading', 'content'],
            [
                ['like' => '%'.$q.'%'],
                ['like' => '%'.$q.'%'],
                ['like' => '% '.$q.' %']
            ]
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
        $this->_addBreadcrumbs($title);
        $this->pageConfig->getTitle()->set($title);

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title)
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __('Blog'),
                    'title' => __('Go to Blog Home Page'),
                    'link' => $this->_url->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('blog_search', ['label' => $title, 'title' => $title]);
        }
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
