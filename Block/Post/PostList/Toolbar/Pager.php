<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\PostList\Toolbar;

use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Model\Config\Source\LazyLoad;
use Magefan\Blog\Model\Config;

/**
 * Blog posts list toolbar pager
 */
class Pager extends \Magento\Theme\Block\Html\Pager
{

    /**
     * Retrieve url of all pages
     *
     * @return string
     */
    public function getPagesUrls()
    {
        $urls = [];
        for ($page = $this->getCurrentPage() + 1; $page <= $this->getLastPageNum(); $page++) {
            $urls[$page] = $this->getPageUrl($page);
        }

        return $urls;
    }

    /**
     * Retrieve true olny if can use lazyload
     *
     * @return bool
     */
    public function useLazyload()
    {
        $lastPage = $this->getLastPageNum();
        $currentPage = $this->getCurrentPage();

        return $this->getLazyloadMode()
            && $this->getCollection()->getSize()
            && $lastPage > 1
            && $currentPage < $lastPage;
    }

    /**
     * Retrieve lazyload json config string
     * @param array $config
     *
     * @return string
     */
    public function getLazyloadConfig(array $config = [])
    {
        $config = array_merge([
            'page_url' => $this->getPagesUrls(),
            'current_page' => $this->getCurrentPage(),
            'last_page' => $this->getLastPageNum(),
            'padding' => $this->getLazyloadPadding(),
            'list_wrapper' => $this->getListWrapper(),
            'auto_trigger' => $this->getLazyloadMode() == LazyLoad::ENABLED_WITH_AUTO_TRIGER,
        ], $config);

        return json_encode($config);
    }

    /**
     * Retrieve lazyload mod
     *
     * @return int
     */
    public function getLazyloadMode()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/post_list/lazyload_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve lazyload padding
     *
     * @return int
     */
    public function getLazyloadPadding()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/post_list/lazyload_padding',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get page pagination type
     *
     * @return string
     */
    public function getPagePaginationType()
    {
        if ($this->_scopeConfig->getValue(Config::XML_PATH_ADVANCED_PERMALINK_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return $this->_scopeConfig->getValue(
                Config::XML_PATH_PAGE_PAGINATION_TYPE,
                ScopeInterface::SCOPE_STORE
            );
        }

        return 'page';
    }

    /**
     * Retrieve page URL by defined parameters
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_fragment'] = $this->getFragment();
        $urlParams['_query'] = $params;

        $pageNumber = $params['page'] ?? ($params['p'] ?? null);
        if ($this->getPagePaginationType() !== '2') {
            $urlParams['_query'] = [$this->getPagePaginationType() => $pageNumber];
            $url = $this->getUrl($this->getPath(), $urlParams);
        } else {
            unset($urlParams['_current']);
            unset($urlParams['_query']);
            unset($urlParams['_fragment']);
            unset($urlParams['_escape']);

            $page = '';
            if ($pageNumber) {
                $page = '/page/' . $params['page'];
            }
            $url = $this->getUrl($this->getPath(), $urlParams);
            if ($parsed = explode('/', parse_url($url)['path'])) {
                $key = array_search('page', $parsed);
                if ($key && isset($parsed[$key + 1]) && intval($parsed[$key + 1])) {
                    $url = str_replace('/page/' . $parsed[$key + 1], $page, $url);
                } else {
                    $url = $url . $page;
                }
            }
        }

        return $url;
    }
}
