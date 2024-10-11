<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Category;

use Magefan\Blog\Block\Post\PostList\Toolbar;
use Magento\Store\Model\ScopeInterface;

/**
 * DEPRECATED !!!!
 *
 * Blog category view
 */
class View extends \Magefan\Blog\Block\Post\PostList
{
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        if ($category = $this->getCategory()) {
            $this->_postCollection->addCategoryFilter($category);
        }
    }

    /**
     * Retrieve category instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_blog_category');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $category = $this->getCategory();
        if ($category) {
            $this->_addBreadcrumbs($category);
            $this->pageConfig->addBodyClass('blog-category-' . $category->getIdentifier());
            $this->pageConfig->getTitle()->set($category->getMetaTitle());
            $this->pageConfig->setKeywords($category->getMetaKeywords());
            $this->pageConfig->setDescription($category->getMetaDescription());

            if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_CATEGORY)) {

                $layoutUpdate = $category->getData('layout_update_xml') ?: '';
                if (false === strpos($layoutUpdate, 'rel="canonical"')) {
                    $canonicalUrl = $category->getCanonicalUrl();
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
            }

            $robots = $category->getData('meta_robots');
            if ($robots) {
                $this->pageConfig->setRobots($robots);
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($category->getTitle())
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @param  string $key
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title = null, $key = null)
    {
        parent::_addBreadcrumbs();
        if ($breadcrumbsBlock = $this->getBreadcrumbsBlock()) {
            $category = $this->getCategory();
            $parentCategories = [];
            while ($parentCategory = $category->getParentCategory()) {
                $parentCategories[] = $category = $parentCategory;
            }

            for ($i = count($parentCategories) - 1; $i >= 0; $i--) {
                $category = $parentCategories[$i];
                $breadcrumbsBlock->addCrumb('blog_parent_category_' . $category->getId(), [
                    'label' => $category->getTitle(),
                    'title' => $category->getTitle(),
                    'link'  => $category->getCategoryUrl()
                ]);
            }

            $category = $this->getCategory();
            $breadcrumbsBlock->addCrumb('blog_category', [
                'label' => $category->getTitle(),
                'title' => $category->getTitle()
            ]);
        }
    }

    /**
     * Retrieve identities
     *
     * @return string
     */
    public function getIdentities()
    {
        return $this->getCategory() ? $this->getCategory()->getIdentities() : [];
    }
}
