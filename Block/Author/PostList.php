<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
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
            $this->_addBreadcrumbs($author->getTitle(), 'blog_author');
            $this->pageConfig->addBodyClass('blog-author-' . $author->getIdentifier());
            $this->pageConfig->getTitle()->set($author->getMetaTitle());
            $this->pageConfig->setKeywords($author->getMetaKeywords());
            $this->pageConfig->setDescription($author->getMetaDescription());

            if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_AUTHOR)) {
                $this->pageConfig->addRemotePageAsset(
                    $author->getAuthorUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }
            $page = $this->_request->getParam(\Magefan\Blog\Block\Post\PostList\Toolbar::PAGE_PARM_NAME);
            if ($page < 2) {
                $robots = $this->config->getAuthorRobots();
                $this->pageConfig->setRobots($robots);
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($author->getTitle())
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Get template type
     *
     * @return string
     */
    public function getPostTemplateType()
    {
        $template = (string)$this->_scopeConfig->getValue(
            'mfblog/author/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template) {
            return $template;
        }

        $template = (string)$this->getAuthor()->getData('posts_list_template');
        if ($template) {
            return $template;
        }

        return parent::getPostTemplateType();
    }

    /**
     * Retrieve Toolbar Block
     * @return \Magefan\Blog\Block\Post\PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        $toolBarBlock = parent::getToolbarBlock();
        $limit = (int)$this->getAuthor()->getData('posts_per_page');

        if ($limit) {
            $toolBarBlock->setData('limit', $limit);
        }

        return $toolBarBlock;
    }
}
