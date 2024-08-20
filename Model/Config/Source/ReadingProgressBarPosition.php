<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Reading Bar types
 *
 */
class ReadingProgressBarPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const string
     */
    const TOP = 'top';

    /**
     * @const string
     */
    const BOTTOM = 'bottom';

    /**
     * @const string
     */
    const LEFT = 'left';

    /**
     * @const string
     */
    const RIGHT = 'right';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOP, 'label' => __('Top (Horizontal)')],
            ['value' => self::BOTTOM, 'label' => __('Bottom (Horizontal)')],
            ['value' => self::LEFT, 'label' => __('Left (Vertical)')],
            ['value' => self::RIGHT, 'label' => __('Right (Vertical)')],
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
