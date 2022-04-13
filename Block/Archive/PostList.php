<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Archive;

use Magefan\Blog\Block\Post\PostList\Toolbar;
use Magento\Store\Model\ScopeInterface;

/**
 * Blog archive posts list
 */
class PostList extends \Magefan\Blog\Block\Post\PostList
{
    /**
     * Prepare posts collection
     * @return \Magefan\Blog\Model\ResourceModel\Post\Collection
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addArchiveFilter(
            $this->getYear(),
            $this->getMonth()
        );
    }

    /**
     * Get archive month
     * @return string
     */
    public function getMonth()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_month');
    }

    /**
     * Get archive year
     * @return string
     */
    public function getYear()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_year');
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

        if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_ARCHIVE)) {

            $canonicalUrl = $this->_url->getUrl(
                $this->getYear() . '-' . str_pad($this->getMonth(), 2, '0', STR_PAD_LEFT),
                \Magefan\Blog\Model\Url::CONTROLLER_ARCHIVE
            );
            $page = (int)$this->_request->getParam(Toolbar::PAGE_PARM_NAME);
            if ($page > 1) {
                $canonicalUrl .= ((false === strpos($canonicalUrl, '?')) ? '?' : '&')
                    . Toolbar::PAGE_PARM_NAME . '=' . $page;
            }

            $this->pageConfig->addRemotePageAsset(
                $canonicalUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $this->pageConfig->setRobots('NOINDEX,FOLLOW');

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(
                $this->escapeHtml($title)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve title
     * @return string
     */
    protected function _getTitle()
    {
        $time = strtotime($this->getYear().'-'.$this->getMonth().'-01');
        return sprintf(
            __('Monthly Archives: %s %s'),
            __(date('F', $time)),
            date('Y', $time)
        );
    }
}
