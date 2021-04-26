<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Tag;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog tag posts list
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
        if ($tag = $this->getTag()) {
            $this->_postCollection->addTagFilter($tag);
        }
    }

    /**
     * Retrieve tag instance
     *
     * @return \Magefan\Blog\Model\Tag
     */
    public function getTag()
    {
        return $this->_coreRegistry->registry('current_blog_tag');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($tag = $this->getTag()) {
            $this->_addBreadcrumbs($tag->getTitle(), 'blog_tag');
            $this->pageConfig->addBodyClass('blog-tag-' . $tag->getIdentifier());
            $this->pageConfig->getTitle()->set($tag->getMetaTitle());
            $this->pageConfig->setKeywords($tag->getMetaKeywords());
            $this->pageConfig->setDescription($tag->getMetaDescription());

            $page = $this->_request->getParam(\Magefan\Blog\Block\Post\PostList\Toolbar::PAGE_PARM_NAME);
            if ($page < 2) {
                $robots = $tag->getData('meta_robots');
                if ($robots) {
                    $this->pageConfig->setRobots($robots);
                } else {
                    $robots = $this->config->getTagRobots();
                    $this->pageConfig->setRobots($robots);
                }
            }

            if ($this->config->getDisplayCanonicalTag(\Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_TAG)) {
                $this->pageConfig->addRemotePageAsset(
                    $tag->getTagUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($tag->getTitle())
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
        $template = (string)$this->getTag()->getData('posts_list_template');
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
        $limit = (int)$this->getTag()->getData('posts_per_page');

        if ($limit) {
            $toolBarBlock->setData('limit', $limit);
        }

        return $toolBarBlock;
    }
}
