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
class Popular extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'popular_posts';

    /**
     * @return $this
     */
    public function _construct()
    {
        $this->setPageSize((int)$this->_getConfigValue('posts_per_page'));
        return parent::_construct();
    }

    /**
     * Retrieve collection order field
     *
     * @return string
     */
    public function getCollectionOrderField()
    {
        return 'views_count';
    }

    /**
     * Retrieve true if display the post image is enabled in the config
     * @return bool
     */
    public function getDisplayImage()
    {
        return (bool)$this->_getConfigValue('display_image');
    }

    public function getClass() {
        $templateName = (string)$this->_getConfigValue('template');
        return $this->templatePool->getTemplateClass('blog_post_sidebar_posts', $templateName);
    }
    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $this->setPageSize((int)$this->_getConfigValue('posts_per_page'));
        $templateName = (string)$this->_getConfigValue('template');

        if (strpos((string) parent::getTemplate(), 'article.phtml') !== false) {
            return parent::getTemplate();
        }

        if ($template = $this->templatePool->getTemplate('blog_post_sidebar_posts', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }

    /**
     * @param $param
     * @return mixed
     */
    protected function _getConfigValue($param)
    {
        return $this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey . '/' . $param,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
