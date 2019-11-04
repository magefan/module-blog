<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post;

/**
 * Class Info
 */
class FbApi extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Info constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isEnabled() && $this->getCommentType() == 'facebook' && $this->isApiEnabled())
            return '<meta property="fb:app_id" content="'. $this->getApiId() . '" />';
    }

    /**
     * @return mixed
     */
    public function getCommentType()
    {
        return $this->_scopeConfig->getValue("mfblog/post_view/comments/type");
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue("mfblog/general/enabled");
    }

    /**
     * @return mixed
     */
    public function getApiId()
    {
        return $this->_scopeConfig->getValue("mfblog/post_view/comments/fb_app_id");
    }

    /**
     * @return mixed
     */
    public function isApiEnabled()
    {
        return $this->_scopeConfig->getValue("mfblog/post_view/comments/fb_app_enabled");
    }
}
