<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar categories block
 */
class Recent extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'recent_posts';

    /**
     * @return $this
     */
    public function _construct()
    {
        $this->setPageSize(
            (int) $this->_scopeConfig->getValue(
                'mfblog/sidebar/'.$this->_widgetKey.'/posts_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        return parent::_construct();
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addRecentFilter();
    }

    /**
     * Retrieve true if display the post image is enabled in the config
     * @return bool
     */
    public function getDisplayImage()
    {
        $designVersion = (string)$this->_scopeConfig->getValue(
            'mfblog/design/version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($designVersion == '2025-04') {
            return false;
        }
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/display_image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getClass() {
        return (string)$this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/template_new',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $designVersion = (string)$this->_scopeConfig->getValue(
            'mfblog/design/version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $templateName = (string)$this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($designVersion == '2025-04') {
            $template = $this->_scopeConfig->getValue(
                'mfblog/sidebar/'.$this->_widgetKey.'/template_new',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if (strpos((string) parent::getTemplate(), 'article.phtml') !== false) {
                return parent::getTemplate();
            }
            if ($template == 'default') {
                $templateName = 'modern';
            } else {
                return 'Magefan_BlogExtra::sidebar/recent_2025_04.phtml';
            }
        }
        
        if ($template = $this->templatePool->getTemplate('blog_post_sidebar_posts', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }
}
