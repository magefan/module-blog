<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Class PostsSortBy Model
 */
class PostsSortBy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const int
     */
    const PUBLISH_DATE = 0;

    /**
     * @const int
     */
    const POSITION = 1;

    /**
     * @const int
     */
    const TITLE = 2;

    /**
     * @const int
     */
    const END_DATE_ASC = 100;

    /**
     * @const int
     */
    const END_DATE_DESC = 101;

    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::PUBLISH_DATE, 'label' => __('Publish Date (default)')],
            ['value' => self::POSITION, 'label' => __('Position')],
            ['value' => self::TITLE, 'label' => __('Title')],
            ['value' => self::END_DATE_ASC, 'label' => __('End Date - Ascending orde - Blog Extra')],
            ['value' => self::END_DATE_DESC, 'label' => __('End Date - Descending order - Blog Extra')]
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
