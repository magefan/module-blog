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
 * Blog post info block
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Block template file
     * @var string
     */
    protected $_template = 'Magefan_Blog::post/info.phtml';

    /**
     * DEPRECATED METHOD!!!!
     * Retrieve formated posted date
     * @var string
     * @return string
     */
    public function getPostedOn($format = 'Y-m-d H:i:s')
    {
        return $this->getPost()->getPublishDate($format);
    }

    /**
     * Retrieve 1 if display author information is enabled
     * @return int
     */
    public function authorEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 1 if author page is enabled
     * @return int
     */
    public function authorPageEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/page_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve true if magefan comments are enabled
     * @return bool
     */
    public function magefanCommentsEnabled()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == \Magefan\Blog\Model\Config\Source\CommetType::MAGEFAN;
    }

    /**
     * @return mixed
     */
    public function getPublishDate()
    {
        $dateFormat = $this->_scopeConfig->getValue(
            'mfblog/design/format_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->getPost()->getPublishDate($dateFormat);
    }

    /**
     * @return bool
     */
    public function isPublicationDateEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/design/publication_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return bool
     */    
    public function getViewsCount()
    {
        return true;
    }
}
