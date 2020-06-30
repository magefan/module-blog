<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post\View\Comments;

/**
 * Class HeadFbApi
 */
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
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isEnabled() && $this->getCommentType() == 'facebook' && $this->isHeadApiEnabled() && $this->getApiId()) {
            return '<meta property="fb:app_id" content="' . $this->escapeHtml($this->getApiId()) . '" />';
        }
    }

    /**
     * @return mixed
     */
    protected function isEnabled()
    {
        return $this->scopeConfig->getValue("mfblog/general/enabled");
    }

    /**
     * @return mixed
     */
    protected function getCommentType()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/type");
    }

    /**
     * @return mixed
     */
    protected function getApiId()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/fb_app_id");
    }

    /**
     * @return mixed
     */
    protected function isHeadApiEnabled()
    {
        return $this->scopeConfig->getValue("mfblog/post_view/comments/fb_app_id_header");
    }
}
