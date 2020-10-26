<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View;

use \Magefan\Blog\Block\Post\AbstractPost;

/**
 * Class Views Counter Block
 */
class ViewsCount extends AbstractPost
{
    /**
     * Retrieve counter controller url
     * @return string
     */
    public function getCounterUrl()
    {
        return $this->getUrl('blog/post/viewscount', [
            'id' => $this->getPost()->getId()
        ]);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_scopeConfig->getValue(
            'mfblog/post_view/views_count/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return parent::_toHtml();
        }
        return '';
    }
}
