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
        'gpt-image-2',
        'gpt-image-2.5-sunburst',
        'gpt-image-2.5-flare'
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