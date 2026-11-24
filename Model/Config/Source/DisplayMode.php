<?php
/**
 * Copyright © 2015-17 Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

/**
 * Comment statuses
 */
class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const string
     */
    public const PENDING = 0;

    /**
     * @const int
     */
    public const APPROVED = 1;

    /**
     * @const int
     */
    public const BLANK = 2;
    
    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::PENDING, 'label' => __('Recent Blog Posts')],
            ['value' => self::APPROVED, 'label' => __('Featured Blog Posts')],
            ['value' => self::BLANK, 'label' => __('Blank (Use widgets to build the page content)')],

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
