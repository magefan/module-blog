<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Category;

use Magento\Store\Model\ScopeInterface;

/**
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
            $categories = $category->getChildrenIds();
            $categories[] = $category->getId();
            $this->_postCollection->addCategoryFilter($categories);
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
            $this->pageConfig->addRemotePageAsset(
                $category->getCategoryUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );

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
            
            $this->addParentCategoriesBreadcrumbs($category, $breadcrumbsBlock);

            $breadcrumbsBlock->addCrumb('blog_category',[
                'label' => $category->getTitle(),
                'title' => $category->getTitle()
            ]);
        }
    }
    /**
     * Get Blog categories as array recursively
     *
     * @param $category
     * @param array $result
     * @return array
     */
    public function getCategoriesAsArrayRecursively($category, $result = []) {

        if ($parentCategory = $category->getParentCategory()) {

            $categoryData = array (
                array (
                    'title' => $parentCategory->getTitle(),
                    'key' => 'blog_parent_category_'.$parentCategory->getId(),
                    'url' => $parentCategory->getCategoryUrl()
                )
            );

            $result = array_merge($result, $this->getCategoriesAsArrayRecursively($parentCategory, $categoryData));
        }

        return $result;

    }

    /**
     * Add blog categories to breadcrumbs
     *
     * @param $category
     * @param $breadcrumbsBlock
     */
    public function addParentCategoriesBreadcrumbs($category, $breadcrumbsBlock) {

        $categoryCollection = $this->getCategoriesAsArrayRecursively($category);

        foreach (array_reverse($categoryCollection) as $category) {

            $breadcrumbsBlock->addCrumb($category['key'], [
                'label' => $category['title'],
                'title' => $category['title'],
                'link'  => $category['url']
            ]);

        }

    }
}
