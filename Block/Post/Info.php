<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

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
     * Retrieve formated posted date
     *
     * @deprecated Use $post->getPublishDate() instead
     * @see \Magefan\Blog\Model\Post::getPublishDate()
     * @param string $format
     * @return string
     */
    public function getPostedOn($format = 'Y-m-d H:i:s')
    {
        return $this->getPost()->getPublishDate($format);
    }

    /**
     * Retrieve 1 if display author information is enabled
     *
     * @return int
     */
    public function authorEnabled(): int
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 1 if author page is enabled
     *
     * @return int
     */
    public function authorPageEnabled(): int
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/page_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve true if magefan comments are enabled
     *
     * @return bool
     */
    public function magefanCommentsEnabled(): bool
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == \Magefan\Blog\Model\Config\Source\CommetType::MAGEFAN;
    }

    /**
     * Retrieve true if views count is enabled
     *
     * @return bool
     */
    public function viewsCountEnabled(): bool
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/post_view/views_count/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 1 if display reading time is enabled
     *
     * @return int
     */
    public function readingTimeEnabled(): int
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/post_view/reading_time/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
