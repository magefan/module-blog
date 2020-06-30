<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Widget;

/**
 * Blog post list block
 * @deprecated Do not use this file! It was taken from the Fastest theme to prevent errors after installing the original version
 */
class PostList extends \Magefan\Blog\Block\Post\PostList\AbstractList implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Block template file
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magefan\Blog\Block\Post\PostList\Toolbar';

    protected function _preparePostCollection()
    {
        //$postCount = empty( $this->getPostCount() )?6:$this->getPostCount();
        $orderBy = $this->getOrderBy();
        $order = $this->getOrder();

        $this->_postCollection = $this->_postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId());

        $this->_postCollection->getSelect()->order($orderBy.' '.$order);
        if ($this->getCategories()) {
            $categories = explode(',', trim($this->getCategories()));
            $this->_postCollection->addCategoryFilter($categories);
        }

        if ($this->getPostCount()) {
            $this->_postCollection->getSelect()->limit($this->getPostCount());
        }

        $this->_postCollection->load();
    }

    public function getPostCollection()
    {
        if (is_null($this->_postCollection)) {
            $this->_preparePostCollection();
        }

        return $this->_postCollection;
    }

    public function getPostedOn($post, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($post->getData('publish_time')));
    }

    public function getOriginalPostImage($post)
    {
        $imgageFile = $post->getPostImage();
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$imgageFile;
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
     * Retrieve Toolbar Block
     * @return \Magefan\Blog\Block\Post\PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();

        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
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

    public function getTemplate()
    {
        $template = $this->getData('post_template');
        if ($template == 'custom') {
            return $this->getData('custom_template');
        } else {
            return $template;
        }
    }
}
