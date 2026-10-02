<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

use Magefan\Blog\Model\TemplatePool;
use Magento\Framework\Module\Manager as ModuleManager;

class PostListWidgetTemplate extends Template
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param TemplatePool $templatePool
     * @param ModuleManager $moduleManager
     * @param string $templateType
     */
    public function __construct(
        TemplatePool $templatePool,
        ModuleManager $moduleManager,
        string $templateType = ''
    ) {
        parent::__construct($templatePool, $templateType);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray():array
    {
        $options = parent::toOptionArray();

        if (!$this->moduleManager->isEnabled('Magefan_BlogExtra')) {
            $filtered = [];
            foreach ($options as $key => $option) {
                if (strpos((string)($option['label'] ?? ''), '(Blog Extra)') === false) {
                    $filtered[$key] = $option;
                }
            }
            $options = $filtered;
        }

        $options['custom_template'] = 'Custom Template';
        return $options;
    }
}
