<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Post;

class CustomCss extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magefan\Blog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magefan\Blog\Model\Config $config
     * @param \Magento\Framework\Registry $_coreRegistry
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magefan\Blog\Model\Config $config,
    \Magento\Framework\Registry $_coreRegistry,
    array $data = []
) {
    $this->config = $config;
    $this->_coreRegistry = $_coreRegistry;
    parent::__construct($context, $data);
}


    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
{
    if ($this->config->isEnabled()) {
        $post = $this->_coreRegistry->registry('current_blog_post');
        if ($post && $post->getCustomCss()) {
            return '<style>' . $post->getCustomCss() . '</style>';
        }
    }

    return '';
}
}