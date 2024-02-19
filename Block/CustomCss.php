<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block;

class CustomCss extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magefan\Blog\Model\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magefan\Blog\Model\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\Config $config,
        array $data = []
    ) {
        $this->config = $config;
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
            if ($css = $this->config->getCustomCss()) {
                return '<style>' . $css . '</style>';
            }
        }

        return '';
    }
}
