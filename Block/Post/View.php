<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post view
 */
class View extends AbstractPost implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Retrieve identities
     *
     * @return string
     */
    public function getIdentities()
    {
        return $this->getPost()->getIdentities();
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $post = $this->getPost();
        if ($post) {
            $this->_addBreadcrumbs($post->getTitle(), 'blog_post');
            $this->pageConfig->addBodyClass('blog-post-' . $post->getIdentifier());
            $this->pageConfig->getTitle()->set($post->getMetaTitle());
            $this->pageConfig->setKeywords($post->getMetaKeywords());
            $this->pageConfig->setDescription($post->getMetaDescription());

            if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_POST)) {
                $this->pageConfig->addRemotePageAsset(
                    $post->getCanonicalUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($post->getTitle())
                );
            }

            if ($post->getIsPreviewMode()) {
                $this->pageConfig->setRobots('NOINDEX,FOLLOW');
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
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $blogTitle = $this->_scopeConfig->getValue(
                'mfblog/index_page/title',
                ScopeInterface::SCOPE_STORE
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __($blogTitle),
                    'title' => __($blogTitle),
                    'link' => $this->_url->getBaseUrl()
                ]
            );

            $parentCategories = [];
            $parentCategory = $this->getPost()->getParentCategory();
            while ($parentCategory) {
                $parentCategories[] = $parentCategory;
                $parentCategory = $parentCategory->getParentCategory();
            }

            for ($i = count($parentCategories) - 1; $i >= 0; $i--) {
                $parentCategory = $parentCategories[$i];
                $breadcrumbsBlock->addCrumb('blog_parent_category_' . $parentCategory->getId(), [
                    'label' => $parentCategory->getTitle(),
                    'title' => $parentCategory->getTitle(),
                    'link'  => $parentCategory->getCategoryUrl()
                ]);
            }

            $breadcrumbsBlock->addCrumb($key, [
                'label' => $title ,
                'title' => $title
            ]);
        }
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $templateName = (string)$this->_scopeConfig->getValue(
            'mfblog/post_view/design/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template = $this->templatePool->getTemplate('blog_post_view', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }
}
