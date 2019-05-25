<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post comments block
 */
class Comments extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $commetType;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magento\Framework\Locale\ResolverInterface      $localeResolver
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Block template file
     * @var string
     */
    protected $_template = 'post/view/comments.phtml';

    /**
     * Retrieve comments type
     * @return bool
     */
    public function getCommentsType()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/type',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function displayPrivacyPolicyCheckbox()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/display_privacy_policy_checkbox',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve number of comments to display
     * @return int
     */
    public function getNumberOfComments()
    {
        return (int)$this->_scopeConfig->getValue(
            'mfblog/post_view/comments/number_of_comments',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve facebook app id
     * @return string
     */
    public function getFacebookAppId()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/fb_app_id',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve disqus forum shortname
     * @return string
     */
    public function getDisqusShortname()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/disqus_forum_shortname',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve locale code
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->_localeResolver->getLocale();
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData(
                'post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->commetType && $this->commetType != $this->getCommentsType()) {
            return '';
        }

        return parent::_toHtml();
    }
}
