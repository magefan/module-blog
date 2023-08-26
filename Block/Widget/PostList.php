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
    
    protected $_sliderData = [];
    
    protected $_show = [];
    
    protected $_themeHelper;
    
    protected $_isFullHtml;
    
    protected function _preparePostCollection()
    {
        $orderBy = $this->getOrderBy();
        $order = $this->getOrder();
        
        $this->_postCollection = $this->_postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder($orderBy, $order);
        
        
        if ($this->getCategories()) {
            $categories = explode(',', trim($this->getCategories()));
            $this->_postCollection->addCategoryFilter($categories);
        }
        
        if ($this->getPostCount()) {
            $this->_postCollection->setPageSize($this->getPostCount());
        }
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
        return date($format, strtotime((string)$post->getData('publish_time')));
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
    /* protected function _beforeToHtml()
    {
        if ($this->isFullHtml()) {
            $toolbar = $this->getToolbarBlock();
            $collection = $this->getPostCollection();
            $toolbar->setCollection($collection);
            $this->setChild('toolbar', $toolbar);
        }
        return parent::_beforeToHtml();
    } */
    public function getTemplate()
    {
        if ($this->isFullHtml()) {
            $template = $this->getData('post_template');
            if ($template == 'custom') {
                return $this->getData('custom_template');
            } else {
                return $template;
            }
        } else {
            return 'Magefan_Blog::post/widget/ajax-blog.phtml';
        }
    }
    
    public function isFullHtml()
    {
        if ($this->_isFullHtml === null) {
            $ajaxBlog = $this->getThemeHelper()->getConfig('pages/blog/use_ajax_blog');
            $this->_isFullHtml = ($this->getData('full_html')) || (!$ajaxBlog);
        }
        return $this->_isFullHtml;
    }
    
    public function getFilterData()
    {
        $data = $this->getData();
        unset($data['type']);
        unset($data['module_name']);
        return $data;
    }
    
    public function getThemeHelper()
    {
        if ($this->_themeHelper === null) {
            $this->_themeHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('Codazon\ThemeLayoutPro\Helper\Data');
        }
        return $this->_themeHelper;
    }
    
    public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'  => (bool)$this->getData('slider_nav'),
                'dots' => (bool)$this->getData('slider_dots')
            ];
            $adapts = ['1900', '1600', '1420', '1280','980','768','480','320','0'];
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
    
    public function subString($str, $strLenght)
    {
        $str = $this->stripTags($str);
        if (strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
    }
    
    public function getElementShow()
    {
        if (!$this->_show) {
            $this->_show = explode(',', $this->getData('show_in_front'));
        }
        return $this->_show;
    }
    
    public function isShow($item)
    {
        return in_array($item, $this->getElementShow());
    }
}
