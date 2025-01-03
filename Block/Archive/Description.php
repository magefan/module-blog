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
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $description = $this->_scopeConfig->getValue(
            'mfblog/archive/meta_description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: $this->_scopeConfig->getValue(
            'mfblog/archive/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $vars = ['year', 'month'];
        $values = [];

        foreach ($vars as $var) {
            $schemaVar = '{{' . $var . '}}';
            if ($description && strpos($description, $schemaVar) !== false) {
                if (!isset($values[$var])) {
                    switch ($var) {
                        case 'year':
                            $values[$var] = date('Y', strtotime((int)$this->_coreRegistry->registry('current_blog_archive_year') . '-01-01'));
                            break;
                        case 'month':
                            $data = (int)$this->_coreRegistry->registry('current_blog_archive_year') . '-' . (int)$this->_coreRegistry->registry('current_blog_archive_month') . '-01';
                            $values[$var] = date('F', strtotime($data));
                            break;
                    }
                }
                $description = str_replace($schemaVar, $values[$var] ?? '', $description);
            }

        }
        return (string)$description;
    }
}
