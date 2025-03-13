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
     * @return int
     */
    public function getMonth()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_month');
    }

    /**
     * Get archive year
     * @return int
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

        $this->pageConfig->setKeywords($this->replaceVars($this->_getConfigValue('meta_keywords')));
        $this->pageConfig->setDescription($this->replaceVars($this->_getConfigValue('meta_description')));

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
     * Retrieve title
     * @return string
     */
    protected function _getTitle()
    {
        return (string)$this->replaceVars($this->_getConfigValue('meta_title') ?: $this->_getConfigValue('title'));
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

    /**
     * @param $content
     * @return array|mixed|string|string[]
     */
    private function replaceVars($content)
    {
        if (!$content) {
            return '';
        }
        $vars = ['year', 'month'];
        $values = [];
        foreach ($vars as $var) {
            $schemaVar = '{{' . $var . '}}';
            if ($content && strpos($content, $schemaVar) !== false) {
                switch ($var) {
                    case 'year':
                        $values[$var] = date('Y', strtotime($this->getYear() . '-01-01'));
                        break;
                    case 'month':
                        $values[$var] = date('F', strtotime($this->getYear() . '-' . $this->getMonth() . '-01'));
                        break;
                }
                $content = str_replace($schemaVar, $values[$var] ?? '', $content);
            }

        }
        return $content;
    }
}
