<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Blog post related posts block
 */
class RelatedPosts extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        $pageSize = (int) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_POSTS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->_postCollection = $this->getPost()->getRelatedPosts()
            ->addActiveFilter()
            ->setPageSize($pageSize ?: 5);

        $this->_postCollection->getSelect()->order('rl.position', 'ASC');
    }

    /**
     * Retrieve true if Display Related Posts enabled
     * @return boolean
     */
    public function displayPosts()
    {
        return (bool) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_POSTS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData(
                'post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * @return string
     */
    public function getBlockTitle() {
        return 'Related Posts';
    }
    /**
     * @return string
     */
    public function getBlockKey() {
        return 'related-post';
    }
    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $designVersion = (string)$this->_scopeConfig->getValue(
            'mfblog/design/version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($designVersion == '2025-04') {
            $template = (string)$this->_scopeConfig->getValue(
                'mfblog/post_view/related_posts/template_new',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (strpos((string) parent::getTemplate(), 'article.phtml') !== false) {
                return parent::getTemplate();
            }
			
            if ($template = $this->templatePool->getTemplate('blog_post_view_related_post_2025_04', $template)) {
                return $template;
            }
        }

        $templateName = (string)$this->_scopeConfig->getValue(
            'mfblog/post_view/related_posts/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template = $this->templatePool->getTemplate('blog_post_view_related_post', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }
}
