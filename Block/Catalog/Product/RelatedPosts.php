<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Catalog\Product;

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
            'mfblog/product_page/number_of_related_posts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$pageSize) {
            $pageSize = 5;
        }
        $this->setPageSize($pageSize);

        parent::_preparePostCollection();

        $product = $this->getProduct();
        $this->_postCollection->getSelect()->joinLeft(
            ['rl' => $product->getResource()->getTable('magefan_blog_post_relatedproduct')],
            'main_table.post_id = rl.post_id AND rl.related_id = ' . (int)$product->getId(),
            ['position']
        )->where(
            'rl.related_id = ?',
            $product->getId()
        );
    }

    /**
     * Retrieve true if Display Related Posts enabled
     *
     * @return boolean
     */
    public function displayPosts(): bool
    {
        return (bool) $this->_scopeConfig->getValue(
            'mfblog/product_page/related_posts_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData(
                'product',
                $this->_coreRegistry->registry('current_product')
            );
        }
        return $this->getData('product');
    }

    /**
     * Retrieve the title of the block if set, otherwise falls back to default
     *
     * @return string
     */
    public function getBlockTitle()
    {
        return $this->getData('block_title') ?: __('Related Posts');
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $templateName = $this->getData('template_type') ?: (string)$this->_scopeConfig->getValue(
            'mfblog/product_page/related_posts_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template = $this->templatePool->getTemplate('blog_post_view_related_post', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }
}
