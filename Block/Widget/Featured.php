<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Widget;

/**
 * Blog featured posts widget
 */

class Featured extends \Magefan\Blog\Block\Sidebar\Featured implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Set blog template
     *
     * @return this
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getData('custom_template') ?: 'Magefan_Blog::widget/recent.phtml'
        );

        return \Magento\Framework\View\Element\Template::_toHtml();
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?: __('Featured Blog Posts');
    }

    /**
     * Retrieve post ids string
     * @return string
     */
    protected function getPostIdsConfigValue()
    {
        return $this->getData('posts_ids');
    }

    /**
     * Retrieve post short content
     * @param  \Magefan\Blog\Model\Post $post
     *
     * @return string
     */
    public function getShorContent($post)
    {
        return $post->getShortFilteredContent();
    }
}
