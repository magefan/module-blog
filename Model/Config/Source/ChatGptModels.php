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

        'gpt-4o',
        'gpt-4o-mini',
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
            $result[] = ['value' => $modelCode, 'label' => $modelCode];
        }

        return $result;
    }
}