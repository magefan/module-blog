<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

class ChatGptImageModels implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string[]
     */
    private $models = [
        'gpt-image-1',
        'gpt-image-2'
    ];

    /**
     * Models which will soon stop being supported
     *
     * @var string[]
     */
    private $deprecatedModels = [
        'gpt-image-1'
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->models as $modelCode) {
            $label = $modelCode;
            if (in_array($modelCode, $this->deprecatedModels, true)) {
                $label .= ' (' . __('Deprecated') . ')';
            }
            $result[] = ['value' => $modelCode, 'label' => $label];
        }

        return $result;
    }
}