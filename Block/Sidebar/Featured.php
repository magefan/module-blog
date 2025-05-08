<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar categories block
 */
class Featured extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'featured_posts';

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addPostsFilter(
            $this->getPostIdsConfigValue()
        );

        $ids = [];
        foreach (explode(',', $this->getPostIdsConfigValue()) as $id) {
            $id = (int)trim($id);
            if ($id) {
                $ids[] = $id;
            }
        }

        if ($ids) {
            $ids = implode(',', $ids);
            $this->_postCollection->getSelect()->order(
                new \Zend_Db_Expr('FIELD(`main_table`.`post_id`,' . $ids .')')
            );
        }
    }

    /**
     * Retrieve post ids string
     * @return string
     */
    protected function getPostIdsConfigValue()
    {
        return (string)$this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/posts_ids',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWidgetKey() {
        return (string)$this->_widgetKey;
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
