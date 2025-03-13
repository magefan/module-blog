<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Archive;

use Magento\Framework\View\Element\Template;

/**
 * Blog index description
 */
class Description extends Template
{
    use Archive;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $description = (string)$this->_scopeConfig->getValue(
            'mfblog/archive/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$description) {
            return '';
        }

        $description = $this->filterContent($description);

        return (string)$description;
    }
}
