<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
}
