<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

class ChatGptModels implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string[]
     */
    private $models = [
        'gpt-5.6',
        'gpt-5.6-terra',
        'gpt-5.6-luna',

        'gpt-5.2',

        'gpt-5',
        'gpt-5-mini',
        'gpt-5-nano',

        'gpt-4.1',
        'gpt-4.1-mini',
        'gpt-4.1-nano',

        'gpt-4',
        'gpt-4o',
        'gpt-4o-mini',

        'gpt-3.5-turbo'
    ];

    /**
     * Models which will soon stop being supported
     *
     * @var string[]
     */
    private $deprecatedModels = [
        'gpt-4.1-nano',
        'gpt-4',
        'gpt-4-turbo',
        'gpt-3.5-turbo'
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