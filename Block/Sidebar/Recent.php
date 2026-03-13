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
class Recent extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'recent_posts';

    /**
     * Retrieve page size
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return (int)$this->getConfigValue('posts_per_page');
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
     *
     * @return bool
     */
    public function getDisplayImage(): bool
    {
        return (bool)$this->getConfigValue('display_image');
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $templateName = $this->getData('template_type') ?: (string)$this->getConfigValue('template');
        if ($template = $this->templatePool->getTemplate('blog_post_sidebar_posts', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }
}
