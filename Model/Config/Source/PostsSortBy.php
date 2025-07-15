<?php

declare(strict_types=1);

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
