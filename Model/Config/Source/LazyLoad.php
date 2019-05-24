<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Lazy load options
 */
class LazyLoad implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLED = 0;
    const ENABLED_WITH_AUTO_TRIGER = 1;
    const ENABLED_WITHOUT_AUTO_TRIGER = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLED, 'label' => __('No')],
            ['value' => self::ENABLED_WITH_AUTO_TRIGER, 'label' => __('Yes (With auto trigger)')],
            ['value' => self::ENABLED_WITHOUT_AUTO_TRIGER, 'label' => __('Yes (Without auto trigger)')],
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
