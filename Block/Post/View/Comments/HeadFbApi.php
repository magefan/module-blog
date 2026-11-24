<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Post\View\Comments;

class HeadFbApi extends \Magento\Framework\View\Element\AbstractBlock
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Info constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Render HTML content.
     *
     * @return string|null
     */
    public function _toHtml()
    {
        if ($this->isEnabled()
            && $this->getCommentType() == 'facebook'
            && $this->isHeadApiEnabled()
            && $this->getApiId()
        ) {
            return '<meta property="fb:app_id" content="' . $this->escapeHtml($this->getApiId()) . '" />';
        }
    }

    /**
     * Checks if the feature is enabled in the configuration.
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->scopeConfig->getValue("mfblog/general/enabled");
    }

    /**
     * Retrieve the comment type configuration value.
     *
     * @return string
     */
    protected function getCommentType()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/type");
    }

    /**
     * Retrieves the Facebook App ID from the configuration settings.
     *
     * @return string|null
     */
    protected function getApiId()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/fb_app_id");
    }

    /**
     * Checks if the Head API is enabled by retrieving the configuration value.
     *
     * @return bool
     */
    protected function isHeadApiEnabled()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/fb_app_id_header");
    }
}
