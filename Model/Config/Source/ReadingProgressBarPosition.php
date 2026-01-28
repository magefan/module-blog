<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

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
    private const TOP = 'top';

    /**
     * @const string
     */
    private const BOTTOM = 'bottom';

    /**
     * @const string
     */
    private const LEFT = 'left';

    /**
     * @const string
     */
    private const RIGHT = 'right';

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
    public function toArray(): array
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
