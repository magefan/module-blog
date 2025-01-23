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
 * Blog post list block
 */
class PostList extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    /**
     * Block template file
     * @var string
     */
    protected $_defaultToolbarBlock = \Magefan\Blog\Block\Post\PostList\Toolbar::class;

    /**
     * @var
     */
    protected $toolbarBlock;

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page = (int)$this->_request->getParam($this->getPageParamName());

        if ($page > 1) {
            //$this->pageConfig->setRobots('NOINDEX,FOLLOW');
            $prefix = (__('Page') . ' ' . $page) . ' - ';
            $this->pageConfig->getTitle()->set(
                $prefix . $this->pageConfig->getTitle()->getShortHeading()
            );
            if ($description = $this->pageConfig->getDescription()) {
                $this->pageConfig->setDescription($prefix . $description);
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $prefix . $pageMainTitle->getPageTitle()
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve post html
     * @param  \Magefan\Blog\Model\Post $post
     * @return string
     */
    public function getPostHtml($post)
    {
        return $this->getChildBlock('blog.posts.list.item')->setPost($post)->toHtml();
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if (!in_array($this->_template, ['post/list.phtml', 'Magefan_Blog::post/list.phtml'])) {
            /* If template was not customized in layout */
            return parent::getTemplate();
        }

        if ($template = $this->templatePool->getTemplate('blog_post_list', $this->getPostTemplateType())) {
            $this->_template = $template;
        }

        return parent::getTemplate();
    }

    /**
     * Get template type
     *
     * @return string
     */
    protected function getPostTemplateType()
    {
        return (string)$this->_scopeConfig->getValue(
            'mfblog/post_list/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Toolbar Block
     * @return \Magefan\Blog\Block\Post\PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        if (null === $this->toolbarBlock) {
            $blockName = $this->getToolbarBlockName();

            if ($blockName) {
                $block = $this->getLayout()->getBlock($blockName);
                if ($block) {
                    $this->toolbarBlock = $block;
                }
            }
            if (!$this->toolbarBlock) {
                $this->toolbarBlock = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
            }
        }

        return $this->toolbarBlock;
    }

    /**
     * Retrieve Toolbar Html
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Before block to html
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getPostCollection();

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);

        return parent::_beforeToHtml();
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
        if ($breadcrumbsBlock = $this->getBreadcrumbsBlock()) {
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
                    'link' => $this->_url->getBaseUrl(),
                ]
            );

            if ($title) {
                $breadcrumbsBlock->addCrumb($key ?: 'blog_item', ['label' => $title, 'title' => $title]);
            }
        }
    }

    /**
     * Retrieve breadcrumbs block
     *
     * @return mixed
     */
    protected function getBreadcrumbsBlock()
    {
        return $this->getLayout()->getBlock('breadcrumbs');
    }
}
