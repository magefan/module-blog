<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Author;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog author posts list
 */
class PostList extends \Magefan\Blog\Block\Post\PostList
{
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        if ($author = $this->getAuthor()) {
            $this->_postCollection->addAuthorFilter($author);
        }
    }

    /**
     * Retrieve author instance
     *
     * @return \Magefan\Blog\Model\Author
     */
    public function getAuthor()
    {
        return $this->_coreRegistry->registry('current_blog_author');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($author = $this->getAuthor()) {
            $this->_addBreadcrumbs($author);
            $this->pageConfig->addBodyClass('blog-author-' . $author->getIdentifier());
            $this->pageConfig->getTitle()->set($author->getTitle());
            $this->pageConfig->addRemotePageAsset(
                $author->getAuthorUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Magefan\Blog\Model\Author $author
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($author)
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

            $breadcrumbsBlock->addCrumb('blog_author',[
                'label' => $author->getTitle(),
                'title' => $author->getTitle()
            ]);
        }
    }
}
