<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\PostList;

/**
 * Post list item
 */
class Item extends \Magefan\Blog\Block\Post\AbstractPost
{
    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = $this->_scopeConfig->getValue(
            'mfblog/post_list/set_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ('custom' == $template) {
            return 'Magefan_Blog::post/list/item-grid.phtml';
        }

        return parent::getTemplate();
    }
}
