<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

class ImageSizeOptions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '256x256', 'label' => __('256x256')],
            ['value' => '512x512', 'label' => __('512x512')],
            ['value' => '1024x1024', 'label' => __('1024x1024')]
            //['value' => '1024x1792', 'label' => __('1024x1792')],
            //['value' => '1792x1024', 'label' => __('1792x1024')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
