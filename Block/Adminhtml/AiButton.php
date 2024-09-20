<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml;

use Magefan\Blog\Model\Config;
use Magento\Backend\Block\Widget\Context;

class AiButton extends \Magento\Backend\Block\Widget\Grid\Container
{
    const ADMIN_RESOURCE = 'Magefan_Blog::post_save';

    /**
     * @param Config $config
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Config $config,
        Context $context,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->config->getConfig('mfblog/ai_writer/enabled_ai_writer')
            && $this->_authorization->isAllowed(self::ADMIN_RESOURCE)
        ) {
            $this->getToolbar()->addChild(
                'ai_button',
                \Magento\Backend\Block\Widget\Button::class,
                ['label' => __('Add With AI'),
                    'onclick' => 'window.location.href = \'' . $this->getUrl('blog/createwithai/index') . '\'']
            );
        }
        return $this;
    }
}
