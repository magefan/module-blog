<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Archive;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog archive posts list
 */
class PostList extends \Magefan\Blog\Block\Post\PostList
{
    use Archive;

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
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->filterContent((string)$this->_getConfigValue('title'));
        $this->_addBreadcrumbs($title, 'blog_search');

        $this->pageConfig->getTitle()->set(
            $this->_getConfigValue('meta_title') ? $this->filterContent($this->_getConfigValue('meta_title')) : $title
        );

        $this->pageConfig->setKeywords($this->filterContent((string)$this->_getConfigValue('meta_keywords')));
        $this->pageConfig->setDescription($this->filterContent((string)$this->_getConfigValue('meta_description')));

        if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_ARCHIVE)) {
            $month = '';
            if ($this->getMonth()) {
                $month = '-' . str_pad($this->getMonth(), 2, '0', STR_PAD_LEFT);
            }
            $canonicalUrl = $this->_url->getUrl(
                $this->getYear() . $month,
                \Magefan\Blog\Model\Url::CONTROLLER_ARCHIVE
            );
            $page = (int)$this->_request->getParam($this->getPageParamName());
            if ($page > 1) {
                $canonicalUrl .= ((false === strpos($canonicalUrl, '?')) ? '?' : '&')
                    . $this->getPageParamName() . '=' . $page;
            }

            $this->pageConfig->addRemotePageAsset(
                $canonicalUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $this->pageConfig->setRobots($this->_getConfigValue('robots'));

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(
                $this->escapeHtml($title)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @param $param
     * @return mixed
     */
    protected function _getConfigValue($param)
    {
        return $this->_scopeConfig->getValue(
            'mfblog/archive/'.$param,
            ScopeInterface::SCOPE_STORE
        );
    }
}
