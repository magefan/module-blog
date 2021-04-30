<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Category;

use Magefan\Blog\Model\Config\Source\CategoryDisplayMode;

/**
 * Blog category posts links
 */
class PostLinks extends \Magefan\Blog\Block\Category\PostList
{
    /**
     * Disable pagination. Display all category posts on the page
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return \Magefan\Blog\Block\Post\PostList\AbstractList::_beforeToHtml();
    }

    /**
     * Retrieve true when display of this block is allowed
     *
     * @return bool
     */
    protected function canDisplay()
    {
        $displayMode = $this->getCategory()->getData('display_mode');
        return ($displayMode == CategoryDisplayMode::POSTS_AND_SUBCATEGORIES_LINKS ||
            $displayMode == CategoryDisplayMode::POST_LINKS);
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
        return null;
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return \Magento\Framework\View\Element\Template::getTemplate();
    }
}
